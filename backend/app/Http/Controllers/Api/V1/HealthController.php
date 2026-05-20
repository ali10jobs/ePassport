<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * @group Health
 *
 * Connectivity and identity probes used for monitoring and the OpenAPI
 * "Try it out" sanity check.
 */
class HealthController extends Controller
{
    /**
     * Health check.
     *
     * Returns service status plus dependency probe results. Used by uptime
     * monitors and the OpenAPI docs landing page.
     *
     * @unauthenticated
     */
    public function check(Request $request): JsonResponse
    {
        $checks = [
            'database' => $this->probe(fn () => DB::connection()->getPdo() !== null),
        ];

        // Only probe Redis when something is actually configured to use it.
        // On the MVP free-tier deploy we route cache/queue/session through
        // postgres + sync drivers, so the PHP redis extension isn't shipped
        // and probing here would falsely flag the service as degraded.
        if ($this->redisInUse()) {
            $checks['redis'] = $this->probe(fn () => Redis::connection()->ping() !== false);
        }

        $healthy = collect($checks)->every(fn (array $r) => $r['ok']);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'service' => 'ePassport API',
            'version' => 'v1',
            'time' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * Authenticated identity.
     *
     * Returns the authenticated user with their organization memberships and
     * roles. Used by both web (cookie session) and mobile (PAT) clients to
     * bootstrap the app shell after login.
     *
     * @authenticated
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // /me is hit on every page navigation in the web frontend and on
        // every cold app launch in mobile. The org membership shape is
        // stable inside a session, so we cache for 60s keyed on user id.
        // The token-based Sanctum auth check still runs on every request,
        // so a revoked token never sees the cached payload.
        $payload = \Illuminate\Support\Facades\Cache::remember(
            "me:user:{$user->id}",
            60,
            fn () => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'locale' => $user->locale,
                'organizations' => $user->organizations->map(fn ($org) => [
                    'id' => $org->id,
                    'name_en' => $org->name_en,
                    'name_ar' => $org->name_ar,
                    'role' => $org->pivot->role,
                    'org_role' => $org->role,
                    'is_default' => (bool) $org->pivot->is_default,
                ])->all(),
            ],
        );

        return response()->json(['data' => $payload]);
    }

    private function redisInUse(): bool
    {
        return config('cache.default') === 'redis'
            || config('queue.default') === 'redis'
            || config('session.driver') === 'redis'
            || config('broadcasting.default') === 'redis';
    }

    /** @return array{ok: bool, error?: string} */
    private function probe(callable $check): array
    {
        try {
            return ['ok' => (bool) $check()];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
