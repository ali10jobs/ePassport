<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('permit_id')->constrained('permits')->cascadeOnDelete();
            $table->foreignUuid('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['permit_id', 'equipment_id']);
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_equipment');
    }
};
