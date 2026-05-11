<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stable per-worker medical profile fields. These are distinct from
 * worker_medical_records (per-exam fitness history) — they hold attributes that
 * rarely change and are surfaced on the gate-scan result screen for on-site
 * medics and first responders.
 *
 * blood_type uses ISBT-style strings ('O+', 'A-', etc.) rather than an enum so
 * unknown/refused values can be stored as null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('blood_type', 3)->nullable()->after('date_of_birth');
            $table->text('allergies')->nullable()->after('blood_type');
            $table->text('chronic_conditions')->nullable()->after('allergies');
            $table->string('emergency_contact_name')->nullable()->after('chronic_conditions');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'blood_type',
                'allergies',
                'chronic_conditions',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
