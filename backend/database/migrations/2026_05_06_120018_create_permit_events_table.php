<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permit lifecycle event log: created, submitted, validated, approved, rejected,
 * closed, expired. Append-only, immutable.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('permit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('permit_id')->constrained('permits')->cascadeOnDelete();
            $table->string('event_type'); // created | submitted | validated | validation_failed | approved | rejected | closed | expired
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('payload')->nullable(); // event-specific data: validation results, rejection reason, etc.
            $table->text('comment')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['permit_id', 'occurred_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_events');
    }
};
