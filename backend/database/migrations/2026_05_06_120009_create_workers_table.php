<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A worker belongs to an employer organization. The worker may be deployed to
 * multiple projects via permits and scans, but employment is per-organization.
 *
 * QR codes: helmet_qr_token and coverall_qr_token are independent random tokens
 * scanned at site gates. Mismatch between scanned helmet and scanned coverall
 * triggers the IMPERSONATION_FLAG.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employer_organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('employee_id'); // employer's internal ID
            $table->string('national_id')->nullable();
            $table->string('iqama_number')->nullable(); // Saudi residency ID for non-Saudis
            $table->string('passport_number')->nullable();
            $table->string('first_name_en');
            $table->string('last_name_en');
            $table->string('first_name_ar')->nullable();
            $table->string('last_name_ar')->nullable();
            $table->string('nationality', 3)->nullable(); // ISO 3166-1 alpha-3
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('trade')->nullable(); // e.g., scaffolder, welder, electrician, supervisor
            $table->string('induction_status')->default('not_inducted'); // not_inducted | inducted | expired
            $table->date('induction_date')->nullable();
            $table->date('induction_valid_until')->nullable();
            $table->string('helmet_qr_token')->unique();
            $table->string('coverall_qr_token')->unique();
            $table->string('photo_path')->nullable(); // signed-URL-served via medialibrary; this caches latest
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employer_organization_id', 'employee_id']);
            $table->index('induction_status');
            $table->index('national_id');
            $table->index('iqama_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
