<?php

namespace App\Exceptions\Api;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

/**
 * Base for stable-coded API errors. The platform's error contract:
 *
 *   HTTP <status>
 *   { "error": {
 *       "code": "<STABLE_CODE>",
 *       "message": "<human readable, may be localized>",
 *       "details": { ... },
 *       "request_id": "<uuid>"
 *   } }
 *
 * Stable codes (UPPER_SNAKE_CASE) are part of the API contract and MUST NOT
 * change. Frontends map them to localized messages. New codes are additive.
 */
class ApiException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $details = [];

    public function __construct(
        protected string $errorCode,
        string $message,
        protected int $status = 400,
        array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
        $this->details = $details;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public function render(\Illuminate\Http\Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'details' => $this->details ?: null,
                'request_id' => $request->attributes->get('request_id'),
            ],
        ], $this->status);
    }
}
