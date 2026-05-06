<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Demo data seeder. Populates orgs, users, project, site, workers, equipment
 * for the demo. Wired in Phase 2.7 after the Worker and Equipment APIs are in
 * place so we know the resulting state matches what the API will read back.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('DemoDataSeeder is a stub; populated in Phase 2.7.');
    }
}
