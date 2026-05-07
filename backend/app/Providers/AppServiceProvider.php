<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->configureRateLimiters();
    }

    /**
     * Per-credential rate limiters. The throttle middleware adds X-RateLimit-*
     * and Retry-After headers automatically; 429 responses are intercepted by
     * the global TooManyRequestsHttpException renderer in bootstrap/app.php
     * and reshaped into the standard {error: {code: RATE_LIMIT_EXCEEDED, ...}}
     * envelope.
     */
    private function configureRateLimiters(): void
    {
        // Authenticated default: 600/minute per user; anonymous fallback: 60/minute per IP.
        RateLimiter::for('api', function (Request $request) {
            $userId = optional($request->user())->getAuthIdentifier();
            $key = $userId ? 'user:'.$userId : 'ip:'.$request->ip();
            $limit = $userId ? 600 : 60;
            return [Limit::perMinute($limit)->by($key)];
        });

        // Public anonymous hazard reporting: protect against abuse without
        // requiring identification. 10/min and 100/hour per IP.
        RateLimiter::for('hazard-anonymous', function (Request $request) {
            $key = 'ip:'.$request->ip();
            return [
                Limit::perMinute(10)->by('m:'.$key),
                Limit::perHour(100)->by('h:'.$key),
            ];
        });

        // Stricter on login to slow credential stuffing: 10/minute per (ip,email).
        RateLimiter::for('login', function (Request $request) {
            $key = 'login:'.$request->ip().':'.strtolower((string) $request->input('email'));
            return [Limit::perMinute(10)->by($key)];
        });
    }
}
