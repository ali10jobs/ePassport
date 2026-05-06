<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stamps every incoming API request with a UUID and echoes it back as the
 * X-Request-Id response header. Used in error payloads, logs, audit log
 * entries, and webhook delivery tracing.
 *
 * If the caller supplies an X-Request-Id we honour it (deduped after basic
 * sanity check) so distributed tracing stays linked end-to-end.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->header('X-Request-Id');
        $requestId = $this->isValidId($incoming) ? $incoming : (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    private function isValidId(?string $candidate): bool
    {
        if ($candidate === null) {
            return false;
        }

        $len = strlen($candidate);

        return $len >= 8 && $len <= 128 && preg_match('/^[A-Za-z0-9_\-:.]+$/', $candidate) === 1;
    }
}
