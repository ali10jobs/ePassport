<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of certification types: NEBOSH IGC, IOSH Managing Safely, Aramco SAEP-X
 * categories, scaffolding (basic/advanced), working at heights, confined space entry,
 * first aid, fire watch, etc. Bilingual.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('certification_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // e.g., NEBOSH_IGC, IOSH_MS, ARAMCO_SAEP_55, SCAFFOLDING_ADV
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('category'); // safety_training | trade_competency | medical | site_induction | tpi
            $table->string('default_issuing_body_en')->nullable();
            $table->string('default_issuing_body_ar')->nullable();
            $table->integer('typical_validity_months')->nullable();
            $table->boolean('requires_document_upload')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_types');
    }
};
