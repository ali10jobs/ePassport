<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of permit-to-work types: hot work, confined space, working at heights,
 * excavation, electrical, lifting (rigging), radiography, etc.
 *
 * Saudi-specific: Aramco SAEP-X permit categories where applicable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // e.g., HOT_WORK, CONFINED_SPACE, WORKING_AT_HEIGHTS, EXCAVATION, ELECTRICAL, LIFTING
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->integer('default_validity_hours')->default(8); // single shift
            $table->boolean('requires_consultant_approval')->default(true);
            $table->boolean('requires_gas_test')->default(false);
            $table->boolean('requires_fire_watch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_types');
    }
};
