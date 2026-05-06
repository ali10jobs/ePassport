<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Catalogs first (independent)
            CertificationTypeSeeder::class,
            PermitTypeSeeder::class,
            // Roles + permissions
            RoleSeeder::class,
            // Demo data depends on catalogs and roles (Phase 2.7)
            DemoDataSeeder::class,
        ]);
    }
}
