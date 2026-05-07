<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('permit_id')->constrained('permits')->cascadeOnDelete();
            $table->foreignUuid('worker_id')->constrained('workers')->restrictOnDelete();
            $table->string('role_on_permit')->default('worker'); // worker | supervisor | gas_tester | fire_watch
            $table->timestamps();

            $table->unique(['permit_id', 'worker_id']);
            $table->index('worker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_workers');
    }
};
