<?php

use App\Models\Equipment;
use App\Models\Organization;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;

beforeEach(fn () => seedAll());

function makeDraftPermit(): string
{
    $project = Project::first();
    $org = Organization::where('default_role', 'main_contractor')->first();
    $hotWork = PermitType::where('code', 'HOT_WORK')->first();

    return Permit::create([
        'permit_number' => 'PRM-2026-T'.bin2hex(random_bytes(2)),
        'project_id' => $project->id,
        'issuing_organization_id' => $org->id,
        'permit_type_id' => $hotWork->id,
        'status' => Permit::STATUS_DRAFT,
        'scope_en' => 'Token attach test',
        'created_by_user_id' => User::first()->id,
    ])->id;
}

it('attaches a worker to a permit via helmet QR token', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $permitId = makeDraftPermit();
    $worker = Worker::first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/workers", [
            'tokens' => [$worker->helmet_qr_token],
        ])
        ->assertOk()
        ->assertJsonPath('data.attached', 1)
        ->assertJsonPath('data.unknown_tokens', []);

    $permit = Permit::find($permitId);
    expect($permit->workers()->where('workers.id', $worker->id)->exists())->toBeTrue();
});

it('attaches a worker via coverall token (same worker resolved)', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $permitId = makeDraftPermit();
    $worker = Worker::first();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/workers", [
            'tokens' => [$worker->coverall_qr_token],
        ])
        ->assertOk()
        ->assertJsonPath('data.attached', 1);

    $permit = Permit::find($permitId);
    expect($permit->workers()->where('workers.id', $worker->id)->exists())->toBeTrue();
});

it('reports unknown tokens without failing the request', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $permitId = makeDraftPermit();
    $worker = Worker::first();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/workers", [
            'tokens' => [$worker->helmet_qr_token, 'totally-bogus-token-zzz'],
        ])
        ->assertOk();

    expect($response->json('data.attached'))->toBe(1);
    expect($response->json('data.unknown_tokens'))->toHaveCount(1);
});

it('attaches equipment via QR token', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $permitId = makeDraftPermit();
    $eq = Equipment::first();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/equipment", [
            'tokens' => [$eq->qr_token],
        ])
        ->assertOk()
        ->assertJsonPath('data.attached', 1);

    $permit = Permit::find($permitId);
    expect($permit->equipment()->where('equipment.id', $eq->id)->exists())->toBeTrue();
});

it('rejects attach attempts on non-draft permits with 409', function () {
    $token = tokenFor('khalid.maincon@epassport.local');

    // Create a permit and force it to closed status
    $permitId = makeDraftPermit();
    Permit::where('id', $permitId)->update(['status' => Permit::STATUS_CLOSED]);

    $worker = Worker::first();
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/workers", [
            'tokens' => [$worker->helmet_qr_token],
        ])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'PERMIT_INVALID_TRANSITION');
});
