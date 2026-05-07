<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic domain event raised whenever a webhook-eligible thing happens. The
 * controller layer raises one of these instead of calling the dispatcher
 * directly; this keeps controllers thin and lets us subscribe new behaviours
 * (audit, analytics) later without touching every controller.
 *
 * Event names match WebhookSubscription event constants exactly:
 *   scan.green, scan.red, scan.impersonation_flag,
 *   permit.{created, submitted, validated, approved, rejected, closed},
 *   hazard_report.{submitted, status_changed, resolved}
 */
final class DomainEvent
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $eventName,
        public readonly array $payload,
        public readonly ?string $ownerOrganizationId = null,
    ) {}
}
