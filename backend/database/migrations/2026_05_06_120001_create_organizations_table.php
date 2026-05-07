<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organizations are companies in the multi-party model:
 * - client (Company A — project owner)
 * - main_contractor (Company B — does the work)
 * - consultant (Company C — supervises B on behalf of A)
 * - subcontractor (Company D — hired by B for specific scopes)
 *
 * The same organization can act as different types on different projects via engagements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('commercial_registration')->nullable()->unique();
            $table->string('vat_number')->nullable();
            $table->string('default_role'); // client | main_contractor | consultant | subcontractor
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('country', 2)->default('SA');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('default_role');
            $table->index('name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
