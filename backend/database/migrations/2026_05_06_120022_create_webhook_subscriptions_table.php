<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Webhook subscriptions. Each owner_organization can subscribe to a set of events;
 * payloads are HMAC-SHA256 signed with the per-subscription secret.
 *
 * Events: scan.green, scan.red, scan.impersonation_flag,
 *         permit.{created,submitted,validated,approved,rejected,closed},
 *         hazard_report.{submitted,status_changed,resolved}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('label');
            $table->string('url');
            $table->string('secret'); // HMAC key; never returned by API after creation
            $table->jsonb('events'); // array of event types subscribed
            $table->boolean('active')->default(true);
            $table->jsonb('headers')->nullable(); // optional custom headers for delivery
            $table->integer('failure_count')->default(0);
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_organization_id');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
