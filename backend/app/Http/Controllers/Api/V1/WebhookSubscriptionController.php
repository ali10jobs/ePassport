<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreWebhookSubscriptionRequest;
use App\Http\Requests\V1\UpdateWebhookSubscriptionRequest;
use App\Models\WebhookSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Webhooks
 *
 * Webhook subscriptions for event delivery to ERP integrations / customer
 * systems. The HMAC secret is returned ONCE at creation time; never echoed
 * again.
 */
class WebhookSubscriptionController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $subs = QueryBuilder::for(WebhookSubscription::class)
            ->allowedFilters([
                AllowedFilter::exact('owner_organization_id'),
                AllowedFilter::exact('active'),
            ])
            ->allowedSorts(['created_at', 'label'])
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->appends($request->query());

        return JsonResource::collection($subs->through(fn (WebhookSubscription $s) => $this->presentWithoutSecret($s)));
    }

    /**
     * Create a webhook subscription. Response includes the secret ONCE; store
     * it client-side immediately, the secret is never returned again.
     *
     * @authenticated
     */
    public function store(StoreWebhookSubscriptionRequest $request): JsonResponse
    {
        $sub = WebhookSubscription::create([
            ...$request->validated(),
            'secret' => $this->generateSecret(),
            'active' => $request->boolean('active', true),
        ]);

        return response()->json([
            'data' => array_merge($this->presentWithoutSecret($sub), [
                'secret' => $sub->getAttributes()['secret'], // raw, ONLY in this response
                'secret_warning' => 'Store the secret now. It will not be shown again. Use it as the HMAC-SHA256 key when verifying X-ePassport-Signature headers.',
            ]),
        ], 201);
    }

    /**
     * @authenticated
     */
    public function show(WebhookSubscription $webhookSubscription): JsonResponse
    {
        return response()->json(['data' => $this->presentWithoutSecret($webhookSubscription)]);
    }

    /**
     * @authenticated
     */
    public function update(UpdateWebhookSubscriptionRequest $request, WebhookSubscription $webhookSubscription): JsonResponse
    {
        $webhookSubscription->update($request->validated());

        return response()->json(['data' => $this->presentWithoutSecret($webhookSubscription->fresh())]);
    }

    /**
     * @authenticated
     */
    public function destroy(WebhookSubscription $webhookSubscription): Response
    {
        $webhookSubscription->delete();

        return response()->noContent();
    }

    /**
     * Rotate the HMAC secret. New secret returned ONCE in this response.
     *
     * @authenticated
     */
    public function rotateSecret(WebhookSubscription $webhookSubscription): JsonResponse
    {
        $newSecret = $this->generateSecret();
        $webhookSubscription->update(['secret' => $newSecret]);

        return response()->json([
            'data' => array_merge($this->presentWithoutSecret($webhookSubscription->fresh()), [
                'secret' => $newSecret,
                'secret_warning' => 'New secret. Update your verifier immediately. The old secret is no longer valid.',
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentWithoutSecret(WebhookSubscription $sub): array
    {
        return [
            'id' => $sub->id,
            'owner_organization_id' => $sub->owner_organization_id,
            'label' => $sub->label,
            'url' => $sub->url,
            'events' => $sub->events,
            'active' => (bool) $sub->active,
            'failure_count' => $sub->failure_count,
            'disabled_at' => $sub->disabled_at?->toIso8601String(),
            'headers' => $sub->headers,
            'created_at' => $sub->created_at?->toIso8601String(),
            'updated_at' => $sub->updated_at?->toIso8601String(),
        ];
    }

    private function generateSecret(): string
    {
        // 64-char hex (256 bits)
        return bin2hex(random_bytes(32));
    }
}
