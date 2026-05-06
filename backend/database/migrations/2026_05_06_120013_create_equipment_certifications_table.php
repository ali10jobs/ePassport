<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Third-Party Inspection (TPI) certificates for equipment. Issued by accredited
 * bodies — TÜV Rheinland, Bureau Veritas, SGS, Lloyd's Register, etc.
 *
 * Equipment is RED at scan time if the latest TPI cert is expired.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('certificate_number')->nullable();
            $table->string('inspection_type'); // periodic | major | post_repair | initial
            $table->string('tpi_body_en'); // TÜV Rheinland, Bureau Veritas, SGS, Lloyd's Register
            $table->string('tpi_body_ar')->nullable();
            $table->string('inspector_name')->nullable();
            $table->date('inspection_date');
            $table->date('expiry_date');
            $table->string('result'); // pass | pass_with_conditions | fail
            $table->text('conditions_en')->nullable();
            $table->text('conditions_ar')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('equipment_id');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_certifications');
    }
};
