<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hazard reports. Anonymity is enforced AT SCHEMA LEVEL: there is NO submitter_id,
 * no IP column, no device_fingerprint column. The anonymous_report_id is a random
 * UUID returned to the submitter so they can check status without identifying.
 *
 * If a report is from an authenticated user (safety champion mode), reporter_user_id
 * is set instead. The two modes are mutually exclusive: anonymous reports never set
 * reporter_user_id. Anonymous_report_id is the public handle in either case.
 *
 * Photos are uploaded with EXIF stripped client-side AND server-side (Intervention).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('anonymous_report_id')->unique(); // public handle for status checks
            $table->boolean('is_anonymous')->default(true);
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete(); // null for anonymous, set for authenticated reports
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('category'); // fall | electrical | fire | working_at_heights | lifting | housekeeping | ppe | environmental | other
            $table->string('severity'); // low | medium | high | critical
            $table->text('description')->nullable();
            $table->string('description_lang', 5)->nullable(); // language of description: en | ar
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('submitted'); // submitted | under_review | action_issued | resolved | dismissed
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('assigned_to_organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->text('resolution_summary')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('severity');
            $table->index('category');
            $table->index('project_id');
            $table->index('assigned_to_organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_reports');
    }
};
