<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A project is owned by a client organization. Other parties (main contractors,
 * consultants, subcontractors) join via engagements.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('code')->unique(); // e.g., ARAMCO-2026-0123
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('status'); // planning | active | on_hold | completed | cancelled
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('client_organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
