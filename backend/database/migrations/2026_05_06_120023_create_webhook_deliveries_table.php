<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-attempt log of webhook deliveries. Drives retry-with-exponential-backoff
 * logic and shows owners delivery history.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('webhook_subscriptions')->cascadeOnDelete();
            $table->string('event'); // event type name
            $table->jsonb('payload');
            $table->string('signature'); // hex HMAC-SHA256 of payload
            $table->integer('attempt_number')->default(1);
            $table->string('status'); // pending | succeeded | failed | dead_lettered
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
            $table->index(['status', 'next_retry_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
