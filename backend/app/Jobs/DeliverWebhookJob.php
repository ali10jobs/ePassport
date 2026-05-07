<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Delivers a single webhook envelope to one subscription. Signs the body with
 * HMAC-SHA256 using the subscription's secret. Writes a WebhookDelivery row
 * for every attempt (success or failure).
 *
 * Retries: 5 attempts with exponential backoff (10s, 30s, 2m, 5m, 15m).
 * After the 5th failure the delivery is marked dead_lettered and the
 * subscription's failure_count is incremented; subscription auto-disables
 * after 25 consecutive failures.
 */
class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var array<int, int> seconds */
    public array $backoff = [10, 30, 120, 300, 900];

    public int $timeout = 30;

    /**
     * @param  array<string, mixed>  $envelope
     */
    public function __construct(
        public readonly string $subscriptionId,
        public readonly array $envelope,
    ) {
    }

    public function handle(): void
    {
        $sub = WebhookSubscription::find($this->subscriptionId);
        if ($sub === null || ! $sub->active) {
            return;
        }

        $bodyJson = json_encode($this->envelope, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $bodyJson, $sub->getAttributes()['secret']);

        $start = microtime(true);
        $delivery = WebhookDelivery::create([
            'subscription_id' => $sub->id,
            'event' => $this->envelope['event'],
            'payload' => $this->envelope,
            'signature' => $signature,
            'attempt_number' => $this->attempts(),
            'status' => WebhookDelivery::STATUS_PENDING,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(array_merge($sub->headers ?? [], [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'ePassport-Webhooks/1.0',
                    'X-ePassport-Event' => $this->envelope['event'],
                    'X-ePassport-Event-Id' => $this->envelope['event_id'],
                    'X-ePassport-Signature' => 'sha256='.$signature,
                    'X-ePassport-Delivery-Attempt' => (string) $this->attempts(),
                ]))
                ->post($sub->url, $this->envelope);

            $durationMs = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $delivery->update([
                    'status' => WebhookDelivery::STATUS_SUCCEEDED,
                    'response_status' => $response->status(),
                    'response_body' => mb_substr($response->body(), 0, 8192),
                    'duration_ms' => $durationMs,
                    'delivered_at' => now(),
                ]);
                $sub->update(['failure_count' => 0]);
                return;
            }

            // Non-2xx: mark this attempt failed and let queue retry
            $delivery->update([
                'status' => WebhookDelivery::STATUS_FAILED,
                'response_status' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 8192),
                'duration_ms' => $durationMs,
                'error_message' => 'Non-2xx response',
                'next_retry_at' => $this->nextRetryAt(),
            ]);
            throw new \RuntimeException("Webhook delivery returned {$response->status()}");
        } catch (Throwable $e) {
            $delivery->update([
                'status' => WebhookDelivery::STATUS_FAILED,
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
                'error_message' => mb_substr($e->getMessage(), 0, 1024),
                'next_retry_at' => $this->nextRetryAt(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        // All retries exhausted
        WebhookDelivery::where('subscription_id', $this->subscriptionId)
            ->where('event', $this->envelope['event'] ?? '')
            ->where('payload->event_id', $this->envelope['event_id'] ?? '')
            ->latest()
            ->limit(1)
            ->update(['status' => WebhookDelivery::STATUS_DEAD_LETTERED]);

        $sub = WebhookSubscription::find($this->subscriptionId);
        if ($sub === null) {
            return;
        }

        $sub->increment('failure_count');
        if ($sub->failure_count >= 25 && $sub->active) {
            $sub->update(['active' => false, 'disabled_at' => now()]);
            Log::warning('Webhook subscription auto-disabled after 25 consecutive failures', [
                'subscription_id' => $sub->id,
                'url' => $sub->url,
            ]);
        }
    }

    private function nextRetryAt(): ?\DateTimeInterface
    {
        $idx = max(0, $this->attempts() - 1);
        $seconds = $this->backoff[$idx] ?? end($this->backoff);
        return now()->addSeconds($seconds);
    }
}
