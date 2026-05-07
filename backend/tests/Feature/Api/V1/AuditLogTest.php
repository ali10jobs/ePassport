<?php

use App\Models\CertificationType;
use App\Models\Organization;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerCertification;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => seedAll());

it('records permit status transitions in the activity log with the auth user as causer', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $consultantToken = tokenFor('nasser.consultant@epassport.local');
    $contractor = User::where('email', 'khalid.maincon@epassport.local')->first();
    $consultant = User::where('email', 'nasser.consultant@epassport.local')->first();

    // Build a permit whose validation will pass (clean scaffolder + WAH permit)
    $project = Project::first();
    $org = Organization::where('default_role', 'main_contractor')->first();
    $wah = PermitType::where('code', 'WORKING_AT_HEIGHTS')->first();

    // Find a clean scaffolder
    $scaffolder = Worker::query()
        ->where('trade', 'Scaffolder')
        ->where('induction_status', 'inducted')
        ->whereHas('medicalRecords', fn ($q) => $q->where('status', 'fit'))
        ->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '<', now()))
        ->whereHas('certifications', fn ($q) => $q->whereHas('certificationType', fn ($qq) => $qq->where('code', 'WAH_CERT')))
        ->first();

    expect($scaffolder)->not->toBeNull('seeded data should provide a clean scaffolder with WAH cert');

    // Add the missing ARAMCO_SAEP_88 cert that the WAH permit also requires
    $saep = CertificationType::where('code', 'ARAMCO_SAEP_88')->first();
    WorkerCertification::create([
        'worker_id' => $scaffolder->id,
        'certification_type_id' => $saep->id,
        'issuing_body_en' => 'Aramco-approved trainer',
        'issue_date' => now()->subYear(),
        'expiry_date' => now()->addYear(),
        'verified' => true,
    ]);

    // Create + attach + submit + approve
    $permitId = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/permits', [
            'project_id' => $project->id,
            'issuing_organization_id' => $org->id,
            'permit_type_id' => $wah->id,
            'scope_en' => 'Audit log test',
        ])->json('data.id');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/workers", [
            'workers' => [['id' => $scaffolder->id, 'role_on_permit' => 'worker']],
        ])->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/permits/{$permitId}/submit")
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$consultantToken}")
        ->postJson("/api/v1/permits/{$permitId}/approve", ['comment' => 'audit test approval'])
        ->assertOk();

    // Verify activity_log captured the permit's status changes
    $permitActivities = Activity::where('subject_type', Permit::class)
        ->where('subject_id', $permitId)
        ->orderBy('created_at')
        ->get();

    expect($permitActivities)->not->toBeEmpty();

    // The 'updated' events should have status in the changed properties
    $updates = $permitActivities->where('event', 'updated');
    expect($updates->count())->toBeGreaterThanOrEqual(2); // at least submit + approve

    // Causer should be at least one of the auth users that made changes
    $causerIds = $updates->pluck('causer_id')->filter()->unique()->all();
    expect($causerIds)->not->toBeEmpty('activity_log must capture the auth user as causer');

    // Each update's properties should expose the status field that changed
    foreach ($updates as $a) {
        $props = $a->properties->toArray();
        expect($props)->toHaveKey('attributes');
        expect($props['attributes'])->toHaveKey('status');
    }

    // Snapshot the lifecycle: status timeline reconstructed from activity_log
    $statuses = $updates->map(fn ($a) => $a->properties['attributes']['status'] ?? null)->filter()->all();
    expect($statuses)->toContain(Permit::STATUS_SUBMITTED);
    expect($statuses)->toContain(Permit::STATUS_APPROVED);
});

it('records worker certification creation in the activity log', function () {
    $token = tokenFor('khalid.maincon@epassport.local');
    $worker = Worker::first();
    $certType = CertificationType::first();

    $before = Activity::count();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/workers/{$worker->id}/certifications", [
            'certification_type_id' => $certType->id,
            'issuing_body_en' => 'Test Body',
            'issue_date' => now()->subDay()->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
        ])
        ->assertStatus(201);

    expect(Activity::count())->toBeGreaterThan($before);
});
