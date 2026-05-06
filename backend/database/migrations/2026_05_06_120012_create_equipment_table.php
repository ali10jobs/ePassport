<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Equipment is owned by an organization. Examples: cranes, scaffolding, lifting gear,
 * man-baskets, generators, welding rigs.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('asset_tag'); // owner's internal ID
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('type'); // crane | scaffold | lifting_gear | man_basket | generator | welding_rig | other
            $table->string('category')->nullable(); // mobile_crane, tower_crane, suspended_scaffold, ...
            $table->date('manufacture_date')->nullable();
            $table->decimal('safe_working_load_kg', 10, 2)->nullable();
            $table->string('qr_token')->unique();
            $table->jsonb('specs')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['owner_organization_id', 'asset_tag']);
            $table->index('type');
            $table->index('owner_organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
