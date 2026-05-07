<?php

use App\Models\Equipment;
use App\Models\Worker;

beforeEach(fn () => seedAll());

it('returns GREEN for a paired operator + valid TPI equipment', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    // Pick equipment with a valid TPI cert
    $eq = Equipment::query()
        ->whereHas('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())
            ->whereIn('result', ['pass', 'pass_with_conditions']))
        ->first();
    expect($eq)->not->toBeNull();

    // Pair a worker as authorised operator
    $worker = Worker::query()
        ->whereHas('certifications')
        ->first();

    $eq->operatorPairings()->create([
        'worker_id' => $worker->id,
        'valid_from' => now()->subDay(),
        'valid_until' => now()->addYear(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify-equipment-operator', [
            'equipment_token' => $eq->qr_token,
            'worker_token' => $worker->helmet_qr_token,
        ])
        ->assertOk()
        ->assertJsonPath('data.result', 'green')
        ->assertJsonPath('data.subject_type', 'equipment')
        ->assertJsonPath('data.subject_id', $eq->id);
});

it('returns RED with OPERATOR_NOT_AUTHORIZED when the worker is not paired', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $eq = Equipment::query()
        ->whereHas('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())
            ->whereIn('result', ['pass', 'pass_with_conditions']))
        ->first();
    $worker = Worker::first();

    // Ensure no pairing exists
    $eq->operatorPairings()->where('worker_id', $worker->id)->forceDelete();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify-equipment-operator', [
            'equipment_token' => $eq->qr_token,
            'worker_token' => $worker->helmet_qr_token,
        ])
        ->assertOk()
        ->assertJsonPath('data.result', 'red');

    expect(collect($response->json('data.reasons'))->pluck('code')->all())
        ->toContain('OPERATOR_NOT_AUTHORIZED');
});

it('returns RED stacking EQUIPMENT_TPI_EXPIRED + OPERATOR_NOT_AUTHORIZED', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $eq = Equipment::query()
        ->whereHas('certifications', fn ($q) => $q->where('expiry_date', '<', now()->toDateString()))
        ->first();
    expect($eq)->not->toBeNull();
    $worker = Worker::first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify-equipment-operator', [
            'equipment_token' => $eq->qr_token,
            'worker_token' => $worker->helmet_qr_token,
        ])
        ->assertOk()
        ->assertJsonPath('data.result', 'red');

    $reasonCodes = collect($response->json('data.reasons'))->pluck('code')->all();
    expect($reasonCodes)->toContain('EQUIPMENT_TPI_EXPIRED');
    expect($reasonCodes)->toContain('OPERATOR_NOT_AUTHORIZED');
});
