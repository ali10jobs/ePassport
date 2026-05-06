<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permit-to-Work (PTW). Lifecycle:
 *   draft -> submitted -> (validated) -> approved -> closed
 *                                     \-> rejected
 *
 * Hard-block on submit: backend re-runs validation against ALL named workers'
 * required certs and ALL named equipment's TPI status. Any failure returns 422
 * with detailed reasons per worker/equipment.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('permit_number')->unique(); // human-readable: PRM-2026-00001
            $table->foreignUuid('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignUuid('issuing_organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('permit_type_id')->constrained('permit_types')->restrictOnDelete();
            $table->string('status'); // draft | submitted | approved | rejected | closed | expired
            $table->text('scope_en');
            $table->text('scope_ar')->nullable();
            $table->string('location_description_en')->nullable();
            $table->string('location_description_ar')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('closure_notes')->nullable();
            $table->jsonb('validation_snapshot')->nullable(); // last validation result for audit
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('project_id');
            $table->index(['valid_from', 'valid_until']);
            $table->index('issuing_organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};
