<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Medical fitness records. The CURRENT (most recent) record drives gate scan
 * green/red on MEDICAL_FAIL.
 *
 * History is preserved (multiple rows per worker) for audit; queries use the
 * latest by exam_date.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->date('exam_date');
            $table->date('valid_until');
            $table->string('status'); // fit | fit_with_restrictions | unfit
            $table->string('examining_clinic_en')->nullable();
            $table->string('examining_clinic_ar')->nullable();
            $table->text('restrictions_en')->nullable();
            $table->text('restrictions_ar')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('worker_id');
            $table->index(['worker_id', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_medical_records');
    }
};
