<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * UNKNOWN_QR scans have no resolved subject. The original schema marked
 * subject_type NOT NULL; relax it so unresolved scans can still be logged
 * for the audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE scan_events ALTER COLUMN subject_type DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE scan_events ALTER COLUMN subject_type SET NOT NULL');
    }
};
