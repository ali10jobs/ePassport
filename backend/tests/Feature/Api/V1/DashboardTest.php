<?php

beforeEach(fn () => seedAll());

it('returns the client dashboard for a client user', function () {
    $token = tokenFor('sara.client@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/dashboards/client/summary')
        ->assertOk()
        ->assertJsonPath('data.role', 'client')
        ->assertJsonStructure(['data' => ['workers', 'certifications', 'permits', 'hazards', 'incident_indicators']]);
});

it('returns the main contractor dashboard scoped to the user org', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/dashboards/main-contractor/summary')
        ->assertOk()
        ->assertJsonPath('data.role', 'main_contractor')
        ->assertJsonStructure(['data' => ['workers' => ['mine', 'subs'], 'equipment' => ['mine', 'tpi_expired'], 'certifications', 'permits']]);
});

it('returns ORG_CONTEXT_MISSING when the role does not match the user', function () {
    $token = tokenFor('sara.client@epassport.local'); // client tries main-contractor

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/dashboards/main-contractor/summary')
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'ORG_CONTEXT_MISSING');
});
