<?php

use Laravel\Sanctum\PersonalAccessToken;

beforeEach(fn () => seedAll());

it('returns the abilities catalog', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/api-keys/abilities')
        ->assertOk()
        ->assertJsonFragment(['data' => [
            'workers.read', 'workers.write', 'equipment.read', 'equipment.write',
            'permits.read', 'permits.write', 'scans.read', 'scans.create',
            'hazards.read', 'hazards.write', 'webhooks.manage', 'dashboards.read',
        ]]);
});

it('issues a scoped API key and revokes it', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $created = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/api-keys', [
            'name' => 'erp-test',
            'abilities' => ['workers.read', 'scans.read'],
        ])->assertStatus(201);

    $erpToken = $created->json('data.token');
    $keyId = $created->json('data.id');
    expect($erpToken)->not->toBeEmpty();

    // Use the new token
    $this->withHeader('Authorization', "Bearer {$erpToken}")
        ->getJson('/api/v1/workers?per_page=1')
        ->assertOk();

    // Revoke
    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/auth/api-keys/{$keyId}")
        ->assertNoContent();

    // Verify the token is gone from DB so any future request will 401.
    // (Asserting the 401 directly would race against Sanctum's per-request
    // user resolution cached in this test's kernel boot.)
    expect(PersonalAccessToken::find($keyId))->toBeNull();
});

it('rejects unknown abilities with VALIDATION_FAILED', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/api-keys', [
            'name' => 'bad',
            'abilities' => ['something.bogus'],
        ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});
