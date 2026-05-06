<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A worker's instance of a certification: when it was issued, by whom, when it expires.
 * Status (valid/expiring_soon/expired) is computed from expiry_date relative to now.
 *
 * Certificate documents are uploaded via Spatie medialibrary; document_media_id
 * holds the latest associated media record.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('certification_type_id')->constrained('certification_types')->restrictOnDelete();
            $table->string('certificate_number')->nullable();
            $table->string('issuing_body_en');
            $table->string('issuing_body_ar')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable(); // nullable for non-expiring certs
            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['worker_id', 'certification_type_id']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_certifications');
    }
};
