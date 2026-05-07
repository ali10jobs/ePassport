<?php

namespace App\Services\Webhook;

use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookSubscription;
use Illuminate\Support\Str;

/**
 * Dispatches a domain event to all matching webhook subscriptions. Each match
 * dispatches one DeliverWebhookJob onto the queue; the job is independently
 * retried with exponential backoff and writes its own WebhookDelivery row.
 *
 * Sender does not block; this returns count of jobs queued.
 */
class WebhookDispatcher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventName, array $payload, ?string $ownerOrganizationId = null): int
    {
        $query = WebhookSubscription::query()->where('active', true);

        // Match the event in the JSON array column. Postgres jsonb @> expects a JSON value.
        $query->whereRaw('events @> ?::jsonb', [json_encode([$eventName])]);

        if ($ownerOrganizationId !== null) {
            $query->where('owner_organization_id', $ownerOrganizationId);
        }

        $eventId = (string) Str::uuid();
        $deliveredAt = now()->toIso8601String();

        $envelope = [
            'event_id' => $eventId,
            'event' => $eventName,
            'occurred_at' => $deliveredAt,
            'data' => $payload,
        ];

        $count = 0;
        $query->cursor()->each(function (WebhookSubscription $sub) use ($envelope, &$count) {
            DeliverWebhookJob::dispatch($sub->id, $envelope);
            $count++;
        });

        return $count;
    }
}
