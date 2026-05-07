<?php

namespace App\Listeners;

use App\Events\DomainEvent;
use App\Services\Webhook\WebhookDispatcher;

class DispatchWebhooksForDomainEvent
{
    public function __construct(private readonly WebhookDispatcher $dispatcher)
    {
    }

    public function handle(DomainEvent $event): void
    {
        $this->dispatcher->dispatch(
            $event->eventName,
            $event->payload,
            $event->ownerOrganizationId,
        );
    }
}
