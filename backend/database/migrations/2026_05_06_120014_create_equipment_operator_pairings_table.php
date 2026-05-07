<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Authorized operator pairings: which workers may operate which equipment.
 * Validated at pairing time (worker must hold the relevant operator certs).
 *
 * Equipment scan returns RED if scanned alongside a worker who is not a paired
 * authorized operator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_operator_pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->foreignId('authorized_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['equipment_id', 'worker_id']);
            $table->index('worker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_operator_pairings');
    }
};
