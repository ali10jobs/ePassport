<?php

use App\Events\DomainEvent;
use App\Jobs\DeliverWebhookJob;
use App\Models\Organization;
use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Bus;

beforeEach(fn () => seedAll());

it('creates a webhook subscription and returns the secret ONCE', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $org = Organization::where('default_role', 'main_contractor')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/webhooks', [
            'owner_organization_id' => $org->id,
            'label' => 'pest hook',
            'url' => 'https://example.test/hook',
            'events' => ['scan.red', 'permit.approved'],
        ]);

    $response->assertStatus(201);
    expect($response->json('data.secret'))->not->toBeEmpty();
    expect(strlen($response->json('data.secret')))->toBe(64);

    // Subsequent show must NOT include the secret
    $subId = $response->json('data.id');
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/webhooks/{$subId}")
        ->assertOk()
        ->assertJsonMissingPath('data.secret');
});

it('rejects unknown event names with VALIDATION_FAILED', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $org = Organization::where('default_role', 'main_contractor')->first();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/webhooks', [
            'owner_organization_id' => $org->id,
            'label' => 'x',
            'url' => 'https://x.test/h',
            'events' => ['totally.fake.event'],
        ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('domain event dispatch matches subscriptions and queues a delivery job', function () {
    Bus::fake();

    $org = Organization::where('default_role', 'main_contractor')->first();
    WebhookSubscription::create([
        'owner_organization_id' => $org->id,
        'label' => 'queue test',
        'url' => 'https://example.test/h',
        'secret' => str_repeat('a', 64),
        'events' => ['scan.red'],
        'active' => true,
    ]);

    DomainEvent::dispatch('scan.red', ['x' => 1]);
    DomainEvent::dispatch('scan.green', ['x' => 2]); // not subscribed

    Bus::assertDispatchedTimes(DeliverWebhookJob::class, 1);
});
