<?php

namespace Database\Seeders;

use App\Models\UserOrganizationRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds platform-wide roles into Spatie laravel-permission. With teams=true,
 * roles can be assigned to a user scoped to a specific organization (set via
 * setPermissionsTeamId() before assignment).
 *
 * The catalog of permissions here will grow as endpoints are added. For Day 1
 * we seed the role names so authorization scaffolding works. Per-feature
 * permissions are added as endpoints are built.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            UserOrganizationRole::ROLE_PLATFORM_ADMIN,
            UserOrganizationRole::ROLE_HSE_MANAGER,
            UserOrganizationRole::ROLE_SAFETY_ENGINEER,
            UserOrganizationRole::ROLE_SUPERVISOR,
            UserOrganizationRole::ROLE_CONSULTANT,
            UserOrganizationRole::ROLE_CLIENT_SAFETY_LEAD,
            UserOrganizationRole::ROLE_AUDITOR,
            UserOrganizationRole::ROLE_WORKER,
        ];

        // Platform-wide permissions (will be expanded per feature)
        $permissions = [
            'workers.view', 'workers.create', 'workers.update', 'workers.delete',
            'equipment.view', 'equipment.create', 'equipment.update', 'equipment.delete',
            'permits.view', 'permits.create', 'permits.submit', 'permits.approve', 'permits.reject', 'permits.close',
            'scans.create', 'scans.view',
            'hazards.view', 'hazards.update', 'hazards.notes.internal', 'hazards.notes.public',
            'dashboards.view',
            'webhooks.manage',
            'api_keys.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'sanctum']);
        }

        // Default permission grants per role; expanded as features land.
        // Platform admin: all
        Role::where('name', UserOrganizationRole::ROLE_PLATFORM_ADMIN)->get()
            ->each(fn (Role $r) => $r->syncPermissions($permissions));
    }
}
