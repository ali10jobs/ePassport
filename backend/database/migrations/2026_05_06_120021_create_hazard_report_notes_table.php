<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notes on hazard reports.
 *
 * note_type:
 *   - internal: visible only to authenticated reviewers (contractor/consultant/client)
 *   - public:   visible on the public anonymous status check page
 *
 * Internal notes MUST NEVER be returned by the public status endpoint.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('hazard_report_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hazard_report_id')->constrained('hazard_reports')->cascadeOnDelete();
            $table->string('note_type'); // internal | public
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('author_organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->text('body');
            $table->string('body_lang', 5)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hazard_report_id', 'note_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_report_notes');
    }
};
