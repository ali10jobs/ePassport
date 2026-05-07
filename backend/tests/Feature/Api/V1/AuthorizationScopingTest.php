<?php

use App\Models\Organization;
use App\Models\User;
use App\Models\UserOrganizationRole;
use App\Models\Worker;

beforeEach(fn () => seedAll());

it('does not expose workers from an org the user is not engaged with', function () {
    // Create an isolated subcontractor org and worker that has NO engagement
    // on the demo project.
    $isolatedOrg = Organization::create([
        'name_en' => 'Isolated Sub Co',
        'name_ar' => 'شركة معزولة',
        'default_role' => Organization::ROLE_SUBCONTRACTOR,
        'country' => 'SA',
        'commercial_registration' => '9999999999',
    ]);

    $isolatedWorker = Worker::create([
        'employer_organization_id' => $isolatedOrg->id,
        'employee_id' => 'ISO-001',
        'first_name_en' => 'Isolated',
        'last_name_en' => 'Worker',
        'helmet_qr_token' => 'iso-h-'.bin2hex(random_bytes(8)),
        'coverall_qr_token' => 'iso-c-'.bin2hex(random_bytes(8)),
        'induction_status' => Worker::INDUCTION_INDUCTED,
        'induction_date' => now()->subDays(10),
        'induction_valid_until' => now()->addMonths(6),
    ]);

    $token = tokenFor('khalid.maincon@epassport.local');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/workers?per_page=100')
        ->assertOk();

    $returnedIds = collect($response->json('data'))->pluck('id')->all();
    expect($returnedIds)->not->toContain($isolatedWorker->id);
});

it('subcontractor sees only their own org workers', function () {
    // The seeded subcontractor user role is on the AL-NAHDI sub. Create a
    // user attached only to that org.
    $sub = Organization::where('default_role', Organization::ROLE_SUBCONTRACTOR)->first();
    $user = User::create([
        'name' => 'Sub User',
        'email' => 'sub.test@epassport.local',
        'password' => bcrypt('password'),
    ]);
    UserOrganizationRole::create([
        'user_id' => $user->id,
        'organization_id' => $sub->id,
        'role' => UserOrganizationRole::ROLE_HSE_MANAGER,
        'is_default' => true,
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/workers?per_page=100')
        ->assertOk();

    $employerOrgIds = collect($response->json('data'))->pluck('employer_organization_id')->unique();
    expect($employerOrgIds->all())->toEqual([$sub->id]);
});
