<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot: which certification types each permit type requires from named workers.
 * Drives the hard-block validation on permit submission.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_type_required_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('permit_type_id')->constrained('permit_types')->cascadeOnDelete();
            $table->foreignUuid('certification_type_id')->constrained('certification_types')->cascadeOnDelete();
            $table->string('applies_to')->default('all'); // all | supervisor_only | worker_only
            $table->timestamps();

            $table->unique(['permit_type_id', 'certification_type_id', 'applies_to'], 'permit_cert_req_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_type_required_certifications');
    }
};
