<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Spatie activitylog defaults subject_id to bigint, but our entities use UUIDs.
 * Cast it to text so the column accepts both UUIDs (entity tables) and bigints
 * (User, pivot tables).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE activity_log ALTER COLUMN subject_id TYPE VARCHAR(64) USING subject_id::text');
        DB::statement('ALTER TABLE activity_log ALTER COLUMN causer_id TYPE VARCHAR(64) USING causer_id::text');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_log ALTER COLUMN subject_id TYPE BIGINT USING NULLIF(subject_id, \'\')::bigint');
        DB::statement('ALTER TABLE activity_log ALTER COLUMN causer_id TYPE BIGINT USING NULLIF(causer_id, \'\')::bigint');
    }
};
