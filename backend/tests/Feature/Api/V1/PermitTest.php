<?php

use App\Models\Organization;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\Project;
use App\Models\Worker;
use App\Models\WorkerCertification;

beforeEach(fn () => seedAll());

it('creates a draft permit with auto permit_number', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $project = Project::first();
    $org = Organization::where('default_role', 'main_contractor')->first();
    $hotWork = PermitType::where('code', 'HOT_WORK')->first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/permits', [
            'project_id' => $project->id,
            'issuing_organization_id' => $org->id,
            'permit_type_id' => $hotWork->id,
            'scope_en' => 'Welding test',
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.status', Permit::STATUS_DRAFT);

    expect($response->json('data.permit_number'))->toMatch('/^PRM-\d{4}-\d{5}$/');
});

it('hard-blocks submit when a named worker has an expired required cert', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $project = Project::first();
    $org = Organization::where('default_role', 'main_contractor')->first();
    $hotWork = PermitType::where('code', 'HOT_WORK')->first();

    // Find a worker with an expired cert
    $cert = WorkerCertification::query()
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<', now()->toDateString())
        ->first();
    $worker = $cert->worker;

    $permit = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/permits', [
            'project_id' => $project->id,
            'issuing_organization_id' => $org->id,
            'permit_type_id' => $hotWork->id,
            'scope_en' => 'Hard-block test',
        ])->json('data.id');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permit}/workers", [
            'workers' => [['id' => $worker->id, 'role_on_permit' => 'worker']],
        ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permit}/submit");

    $response->assertStatus(422)
        ->assertJsonPath('error.code', 'PERMIT_VALIDATION_FAILED');

    $workerFailures = $response->json('error.details.worker_failures');
    expect($workerFailures)->toHaveCount(1);
    expect($workerFailures[0]['worker_id'])->toBe($worker->id);
});

it('rejects submitting a permit not in draft status', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $project = Project::first();
    $org = Organization::where('default_role', 'main_contractor')->first();
    $hotWork = PermitType::where('code', 'HOT_WORK')->first();

    $permit = Permit::create([
        'permit_number' => 'PRM-2026-99999',
        'project_id' => $project->id,
        'issuing_organization_id' => $org->id,
        'permit_type_id' => $hotWork->id,
        'status' => Permit::STATUS_CLOSED,
        'scope_en' => 'Closed permit test',
        'created_by_user_id' => \App\Models\User::first()->id,
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permit->id}/submit")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'PERMIT_INVALID_TRANSITION');
});
