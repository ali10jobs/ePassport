<?php

namespace App\Services\Permit;

use App\Models\Permit;

class PermitNumberGenerator
{
    /**
     * Generate a human-readable permit number: PRM-YYYY-NNNNN.
     * Sequential within the year; collision-tolerant via retry.
     */
    public function next(): string
    {
        $year = now()->year;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $maxThisYear = Permit::where('permit_number', 'like', "PRM-{$year}-%")
                ->orderByDesc('permit_number')
                ->limit(1)
                ->value('permit_number');

            $seq = 1;
            if ($maxThisYear !== null) {
                $parts = explode('-', $maxThisYear);
                $seq = ((int) end($parts)) + 1;
            }

            $candidate = sprintf('PRM-%d-%05d', $year, $seq);
            if (! Permit::where('permit_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Extremely unlikely fallback: timestamp-based
        return sprintf('PRM-%d-%s', $year, substr((string) now()->timestamp, -5));
    }
}
