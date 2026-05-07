<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idempotency-Key support for write endpoints (POST/PUT/PATCH).
 *
 * Behaviour:
 *  - If the caller supplies an Idempotency-Key header AND we've seen it within
 *    the TTL (24h) for this user/route, return the cached response untouched.
 *  - Otherwise pass the request through; on a 2xx response, cache the
 *    response body+status for the TTL.
 *  - On non-write methods, this middleware is a no-op.
 *
 * The key is namespaced by user id (or 'guest' for anonymous routes) and
 * route name so two unrelated callers can share Idempotency-Key strings
 * without collision.
 *
 * Storage: Redis with EX 86400. Behaviour matches Stripe's idempotency
 * semantics for POSTs that mutate state.
 */
class IdempotencyKey
{
    private const TTL_SECONDS = 86400; // 24h per Doc 1

    private const HEADER = 'Idempotency-Key';

    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), self::WRITE_METHODS, true)) {
            return $next($request);
        }

        $key = $request->header(self::HEADER);
        if ($key === null || trim($key) === '') {
            return $next($request);
        }

        if (! $this->isValidKey($key)) {
            return $next($request);
        }

        $cacheKey = $this->buildCacheKey($request, $key);

        $cached = Redis::get($cacheKey);
        if ($cached !== null) {
            $payload = json_decode($cached, true);
            if (is_array($payload) && isset($payload['status'], $payload['body'])) {
                return response($payload['body'], $payload['status'], array_merge(
                    $payload['headers'] ?? [],
                    ['Idempotency-Replayed' => 'true']
                ));
            }
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Redis::setex($cacheKey, self::TTL_SECONDS, json_encode([
                'status' => $response->getStatusCode(),
                'body' => $response->getContent(),
                'headers' => [
                    'Content-Type' => $response->headers->get('Content-Type'),
                ],
            ]));
        }

        return $response;
    }

    private function isValidKey(string $key): bool
    {
        $len = strlen($key);

        return $len >= 8 && $len <= 255 && preg_match('/^[A-Za-z0-9_\-:.]+$/', $key) === 1;
    }

    private function buildCacheKey(Request $request, string $idempotencyKey): string
    {
        $caller = optional($request->user())->getAuthIdentifier() ?? 'guest';
        $route = $request->route()?->getName() ?? $request->path();

        return sprintf('idempotency:%s:%s:%s', $caller, $route, $idempotencyKey);
    }
}
