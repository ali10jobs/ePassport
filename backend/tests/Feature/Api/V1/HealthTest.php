<?php

use Illuminate\Support\Facades\Artisan;

it('returns 200 with the database probe ok', function () {
    // Cache / queue / session use array/sync/array in tests, so the redis
    // probe is skipped — only the database probe runs.
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database.ok', true);
    expect($response->headers->get('X-Request-Id'))->not->toBeEmpty();
});

it('serves the OpenAPI spec at the canonical path', function () {
    Artisan::call('scribe:generate', ['--quiet' => true]);

    $response = $this->getJson('/api/v1/openapi.json');

    $response->assertOk()
        ->assertJsonPath('openapi', '3.1.0');
});
