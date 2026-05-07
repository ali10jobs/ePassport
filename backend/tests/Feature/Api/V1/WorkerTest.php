<?php

use App\Models\Organization;
use App\Models\Worker;

beforeEach(fn () => seedAll());

it('lists workers with pagination', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/workers?per_page=5')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta' => ['total', 'per_page'], 'links']);
});

it('creates a worker and auto-assigns helmet+coverall QR tokens', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $org = Organization::where('default_role', 'main_contractor')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/workers', [
            'employer_organization_id' => $org->id,
            'employee_id' => 'PEST-001',
            'first_name_en' => 'Test',
            'last_name_en' => 'Worker',
            'nationality' => 'SAU',
            'trade' => 'Welder',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.employee_id', 'PEST-001');

    $worker = Worker::where('employee_id', 'PEST-001')->first();
    expect($worker)->not->toBeNull();
    expect($worker->helmet_qr_token)->not->toBeEmpty();
    expect($worker->coverall_qr_token)->not->toBeEmpty();
    expect($worker->helmet_qr_token)->not->toEqual($worker->coverall_qr_token);
});

it('rejects worker create without required fields with VALIDATION_FAILED', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/workers', [])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('returns the consolidated e-Passport view for a worker', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $worker = Worker::first();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/workers/{$worker->id}/passport")
        ->assertOk()
        ->assertJsonPath('data.id', $worker->id)
        ->assertJsonStructure(['data' => ['certifications', 'medical_fitness', 'induction', 'employer']]);
});

it('returns helmet QR as image/png', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $worker = Worker::first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->get("/api/v1/workers/{$worker->id}/qr/helmet");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toBe('image/png');
    expect(substr($response->getContent(), 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});
