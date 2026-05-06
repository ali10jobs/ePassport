<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An engagement places an organization on a project in a specific role.
 * Subcontracts nest via parent_engagement_id (subcontractor under main contractor).
 *
 * Examples on the same project:
 *  - Org B as main_contractor, parent_engagement_id NULL
 *  - Org C as consultant, parent_engagement_id NULL
 *  - Org D as subcontractor, parent_engagement_id = Org B's engagement id
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->uuid('parent_engagement_id')->nullable();
            $table->string('role'); // main_contractor | consultant | subcontractor
            $table->string('scope_en')->nullable();
            $table->string('scope_ar')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status'); // active | suspended | completed | terminated
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'role']);
            $table->index('organization_id');
            $table->index('status');
            $table->index('parent_engagement_id');
        });

        // Self-referencing FK added after table creation so the primary key exists.
        Schema::table('engagements', function (Blueprint $table) {
            $table->foreign('parent_engagement_id')
                ->references('id')->on('engagements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};
