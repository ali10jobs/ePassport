<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // App\Listeners\DispatchWebhooksForDomainEvent is auto-registered by
        // Laravel 11's listener discovery via the typed first parameter of
        // its handle(DomainEvent) method.
    }
}
