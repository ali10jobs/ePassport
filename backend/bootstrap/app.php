<?php

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Middleware\Api\AssignRequestId;
use App\Http\Middleware\Api\IdempotencyKey;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust the platform proxy (Render / Fly / Railway / Cloudflare).
        // Without this, generated absolute URLs (incl. signed photo URLs)
        // keep their original scheme and break behind TLS termination.
        $middleware->trustProxies(at: '*');

        // Sanctum stateful middleware lets cookie-authenticated SPA requests through.
        $middleware->statefulApi();

        // Apply request-id stamping + idempotency to every API route.
        $middleware->api(prepend: [
            AssignRequestId::class,
        ]);

        $middleware->alias([
            'idempotency' => IdempotencyKey::class,
            'request_id' => AssignRequestId::class,
        ]);

        // Idempotency runs after auth so user-scoped keys work.
        $middleware->appendToGroup('api', IdempotencyKey::class);

        // API-only — never redirect unauthenticated requests; let the exception
        // handler render a JSON 401 with the standard error shape.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sentry: forward unhandled exceptions to Sentry when SENTRY_LARAVEL_DSN is set.
        // No-op locally where the env var is empty.
        Integration::handles($exceptions);

        $exceptions->render(function (ApiException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return $e->render($request);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::VALIDATION_FAILED,
                    'message' => $e->getMessage(),
                    'details' => ['fields' => $e->errors()],
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::UNAUTHENTICATED,
                    'message' => 'Authentication required.',
                    'details' => null,
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::FORBIDDEN,
                    'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
                    'details' => null,
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::RESOURCE_NOT_FOUND,
                    'message' => 'The requested resource was not found.',
                    'details' => ['model' => class_basename($e->getModel())],
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::RESOURCE_NOT_FOUND,
                    'message' => 'The requested endpoint or resource was not found.',
                    'details' => null,
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::METHOD_NOT_ALLOWED,
                    'message' => $e->getMessage(),
                    'details' => null,
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 405);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::RATE_LIMIT_EXCEEDED,
                    'message' => 'Too many requests.',
                    'details' => ['retry_after' => $e->getHeaders()['Retry-After'] ?? null],
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], 429);
        });

        // Catch-all for HttpException subclasses we haven't specifically handled
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => ErrorCodes::INTERNAL_ERROR,
                    'message' => $e->getMessage() ?: 'Request failed.',
                    'details' => null,
                    'request_id' => $request->attributes->get('request_id'),
                ],
            ], $e->getStatusCode());
        });
    })->create();
