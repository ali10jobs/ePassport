<?php

it('returns 200 with database + redis probes ok', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database.ok', true)
        ->assertJsonPath('checks.redis.ok', true);
    expect($response->headers->get('X-Request-Id'))->not->toBeEmpty();
});

it('serves the OpenAPI spec at the canonical path', function () {
    \Illuminate\Support\Facades\Artisan::call('scribe:generate', ['--quiet' => true]);

    $response = $this->getJson('/api/v1/openapi.json');

    $response->assertOk()
        ->assertJsonPath('openapi', '3.1.0');
});
