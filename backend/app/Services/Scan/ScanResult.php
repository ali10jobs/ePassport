<?php

namespace App\Services\Scan;

use App\Models\ScanEvent;

/**
 * Structured outcome of a scan verification. Final shape is what the gate-scan
 * UI renders directly: a status (green / red / impersonation_flag) plus an
 * ordered list of reasons. Each reason carries its stable code and any
 * details the UI needs to render the localized message.
 */
final class ScanResult
{
    /**
     * @param  array<int, array{code: string, details?: array<string, mixed>}>  $reasons
     */
    public function __construct(
        public readonly string $result,
        public readonly ?string $subjectType,
        public readonly ?string $subjectId,
        public readonly array $reasons = [],
        public readonly ?string $tokenType = null,
    ) {}

    public function isGreen(): bool
    {
        return $this->result === ScanEvent::RESULT_GREEN;
    }

    public function isRed(): bool
    {
        return $this->result === ScanEvent::RESULT_RED;
    }

    public function isImpersonation(): bool
    {
        return $this->result === ScanEvent::RESULT_IMPERSONATION_FLAG;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'token_type' => $this->tokenType,
            'reasons' => $this->reasons,
        ];
    }
}
