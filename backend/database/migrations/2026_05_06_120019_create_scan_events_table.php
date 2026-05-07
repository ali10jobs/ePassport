<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every gate scan logged here for audit and reporting. Polymorphic subject:
 * subject_type = 'worker' | 'equipment'.
 *
 * Reasons array stores stable codes: CERT_EXPIRED, INDUCTION_MISSING, MEDICAL_FAIL,
 * ORG_NOT_ENGAGED, IMPERSONATION_FLAG, EQUIPMENT_TPI_EXPIRED, OPERATOR_NOT_AUTHORIZED.
 *
 * scanner_user_id may be null for kiosk/anonymous device scans (post-MVP).
 * scan_token: helmet_qr or coverall_qr or equipment_qr that was scanned.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('scanner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('subject_type'); // worker | equipment
            $table->uuid('subject_id')->nullable(); // null when QR resolves to nothing
            $table->string('scan_token_type')->nullable(); // helmet | coverall | equipment | manual
            $table->string('scan_token')->nullable();
            $table->string('result'); // green | red | impersonation_flag
            $table->jsonb('reasons')->nullable(); // [{ code: CERT_EXPIRED, details: {...} }, ...]
            $table->jsonb('paired_scan_data')->nullable(); // for helmet+coverall and equipment+operator pairings
            $table->boolean('is_manual_entry')->default(false);
            $table->boolean('is_offline_originated')->default(false); // for v1.1 offline mobile
            $table->string('client_app')->nullable(); // web | mobile_ios | mobile_android | api
            $table->string('idempotency_key')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('scanner_user_id');
            $table->index('result');
            $table->index('scanned_at');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_events');
    }
};
