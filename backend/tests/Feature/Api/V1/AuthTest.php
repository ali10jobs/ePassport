<?php

beforeEach(fn () => seedAll());

it('logs in via token mode and returns a bearer + user with orgs', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'khalid.maincon@epassport.local',
        'password' => 'password',
        'mode' => 'token',
        'device_name' => 'pest',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure(['data' => ['access_token', 'user' => ['id', 'email', 'organizations']]]);
});

it('returns UNAUTHENTICATED on bad credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'khalid.maincon@epassport.local',
        'password' => 'wrong',
        'mode' => 'token',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('returns UNAUTHENTICATED on /me without token', function () {
    $this->getJson('/api/v1/me')
        ->assertStatus(401)
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});

it('returns user identity with org memberships on /me', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'khalid.maincon@epassport.local')
        ->assertJsonStructure(['data' => ['organizations']]);
});
