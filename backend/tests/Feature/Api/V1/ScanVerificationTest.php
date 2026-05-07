<?php

use App\Models\Equipment;
use App\Models\ScanEvent;
use App\Models\Worker;
use App\Models\WorkerCertification;

beforeEach(fn () => seedAll());

it('returns GREEN for a clean worker scan', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $worker = Worker::query()
        ->where('induction_status', 'inducted')
        ->whereHas('medicalRecords', fn ($q) => $q->where('status', 'fit'))
        ->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '<', now()))
        ->first();
    expect($worker)->not->toBeNull('seeded data should contain at least one clean worker');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify', ['token' => $worker->helmet_qr_token])
        ->assertOk()
        ->assertJsonPath('data.result', 'green')
        ->assertJsonPath('data.subject_type', 'worker')
        ->assertJsonPath('data.subject_id', $worker->id);
});

it('returns RED with CERT_EXPIRED for a worker with an expired certification', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $cert = WorkerCertification::query()
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<', now()->toDateString())
        ->first();
    expect($cert)->not->toBeNull('seeder should plant at least one expired cert');
    $worker = $cert->worker;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify', ['token' => $worker->helmet_qr_token])
        ->assertOk()
        ->assertJsonPath('data.result', 'red');

    $reasonCodes = collect($response->json('data.reasons'))->pluck('code')->all();
    expect($reasonCodes)->toContain('CERT_EXPIRED');
});

it('returns RED with EQUIPMENT_TPI_EXPIRED for equipment with expired TPI', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $eq = Equipment::query()
        ->whereHas('certifications', fn ($q) => $q->where('expiry_date', '<', now()))
        ->first();
    expect($eq)->not->toBeNull();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify', ['token' => $eq->qr_token])
        ->assertOk()
        ->assertJsonPath('data.result', 'red')
        ->assertJsonPath('data.subject_type', 'equipment');

    expect(collect($response->json('data.reasons'))->pluck('code')->all())
        ->toContain('EQUIPMENT_TPI_EXPIRED');
});

it('returns IMPERSONATION_FLAG when helmet and coverall belong to different workers', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    [$a, $b] = Worker::query()->take(2)->get();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify-pair', [
            'helmet_token' => $a->helmet_qr_token,
            'coverall_token' => $b->coverall_qr_token,
        ])
        ->assertOk()
        ->assertJsonPath('data.result', 'impersonation_flag');
});

it('returns RED with UNKNOWN_QR for an unrecognised token', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify', ['token' => 'this-is-not-a-real-token-zzzzzz']);

    $response->assertOk()->assertJsonPath('data.result', 'red');
    expect(collect($response->json('data.reasons'))->pluck('code')->all())->toContain('UNKNOWN_QR');
});

it('writes a ScanEvent row with hashed scan_token (raw token never persisted)', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $worker = Worker::first();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/scans/verify', ['token' => $worker->helmet_qr_token]);

    $event = ScanEvent::latest('scanned_at')->first();
    expect($event)->not->toBeNull();
    // Stored token must be the SHA-256 hex of the raw token, not the raw value
    expect($event->scan_token)->toBe(hash('sha256', $worker->helmet_qr_token));
    expect($event->scan_token)->not->toBe($worker->helmet_qr_token);
});
