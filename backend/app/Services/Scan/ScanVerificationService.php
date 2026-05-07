<?php

namespace App\Services\Scan;

use App\Models\Engagement;
use App\Models\Equipment;
use App\Models\ScanEvent;
use App\Models\Worker;

/**
 * Verifies a QR token against current platform state and produces a structured
 * ScanResult. This is the heart of the gate-scan flow: green/red is decided
 * here; the controller is a thin transport.
 *
 * Verification sequence for a worker scan:
 *   1. resolve token -> worker (via helmet_qr_token OR coverall_qr_token)
 *   2. INDUCTION_MISSING if induction_status != 'inducted' or expired
 *   3. ORG_NOT_ENGAGED if employer org has no active engagement on the
 *      project context (when project_id is supplied)
 *   4. MEDICAL_FAIL if latest medical record is unfit or expired
 *   5. CERT_EXPIRED for each expired cert (does not block scan in v1.0 unless
 *      cert is required for site induction; per-permit cert validation lives
 *      on the permit submit flow)
 *
 * Verification sequence for an equipment scan:
 *   1. resolve token -> equipment
 *   2. EQUIPMENT_TPI_EXPIRED if no valid TPI cert
 *   3. OPERATOR_NOT_AUTHORIZED only when paired with a worker scan (handled
 *      by the verifyPair caller)
 *
 * Result is RED if any reason fired; otherwise GREEN. UNKNOWN_QR if the token
 * resolves to nothing.
 */
class ScanVerificationService
{
    /**
     * @param  array<string, mixed>  $context  Optional: project_id, site_id
     */
    public function verifyToken(string $token, array $context = []): ScanResult
    {
        // Try worker (helmet) first
        $worker = Worker::where('helmet_qr_token', $token)->first();
        if ($worker !== null) {
            return $this->verifyWorker($worker, ScanEvent::TOKEN_HELMET, $context);
        }

        $worker = Worker::where('coverall_qr_token', $token)->first();
        if ($worker !== null) {
            return $this->verifyWorker($worker, ScanEvent::TOKEN_COVERALL, $context);
        }

        $equipment = Equipment::where('qr_token', $token)->first();
        if ($equipment !== null) {
            return $this->verifyEquipment($equipment, $context);
        }

        return new ScanResult(
            result: ScanEvent::RESULT_RED,
            subjectType: null,
            subjectId: null,
            reasons: [['code' => ScanEvent::REASON_UNKNOWN_QR, 'details' => ['token' => substr($token, 0, 8).'...']]],
            tokenType: ScanEvent::TOKEN_MANUAL,
        );
    }

    /**
     * Cross-check helmet + coverall scans for the same worker. Two distinct
     * tokens in sequence; if they don't resolve to the same worker, this is
     * an IMPERSONATION_FLAG. Otherwise, run the standard worker verification
     * once and return the result.
     */
    public function verifyPair(string $helmetToken, string $coverallToken, array $context = []): ScanResult
    {
        $helmetWorker = Worker::where('helmet_qr_token', $helmetToken)->first();
        $coverallWorker = Worker::where('coverall_qr_token', $coverallToken)->first();

        if ($helmetWorker === null || $coverallWorker === null) {
            $missing = [];
            if ($helmetWorker === null) {
                $missing[] = 'helmet';
            }
            if ($coverallWorker === null) {
                $missing[] = 'coverall';
            }

            return new ScanResult(
                result: ScanEvent::RESULT_RED,
                subjectType: ScanEvent::SUBJECT_WORKER,
                subjectId: $helmetWorker?->id ?? $coverallWorker?->id,
                reasons: [['code' => ScanEvent::REASON_UNKNOWN_QR, 'details' => ['missing' => $missing]]],
                tokenType: ScanEvent::TOKEN_HELMET,
            );
        }

        if ($helmetWorker->id !== $coverallWorker->id) {
            return new ScanResult(
                result: ScanEvent::RESULT_IMPERSONATION_FLAG,
                subjectType: ScanEvent::SUBJECT_WORKER,
                subjectId: $helmetWorker->id,
                reasons: [[
                    'code' => ScanEvent::REASON_IMPERSONATION_FLAG,
                    'details' => [
                        'helmet_worker_id' => $helmetWorker->id,
                        'coverall_worker_id' => $coverallWorker->id,
                    ],
                ]],
                tokenType: ScanEvent::TOKEN_HELMET,
            );
        }

        // Same worker — run standard checks
        return $this->verifyWorker($helmetWorker, ScanEvent::TOKEN_HELMET, $context);
    }

    /**
     * Verify equipment + a paired worker scan. Adds OPERATOR_NOT_AUTHORIZED
     * if the worker is not on the equipment's operator pairings list.
     */
    public function verifyEquipmentWithOperator(string $equipmentToken, string $workerToken, array $context = []): ScanResult
    {
        $equipment = Equipment::where('qr_token', $equipmentToken)->first();
        if ($equipment === null) {
            return new ScanResult(
                result: ScanEvent::RESULT_RED,
                subjectType: null,
                subjectId: null,
                reasons: [['code' => ScanEvent::REASON_UNKNOWN_QR, 'details' => ['side' => 'equipment']]],
                tokenType: ScanEvent::TOKEN_EQUIPMENT,
            );
        }

        $worker = Worker::where('helmet_qr_token', $workerToken)
            ->orWhere('coverall_qr_token', $workerToken)
            ->first();

        $base = $this->verifyEquipment($equipment, $context);
        $reasons = $base->reasons;

        if ($worker === null) {
            $reasons[] = ['code' => ScanEvent::REASON_UNKNOWN_QR, 'details' => ['side' => 'operator']];
        } else {
            $authorized = $equipment->authorizedOperators()->where('workers.id', $worker->id)->exists();
            if (! $authorized) {
                $reasons[] = [
                    'code' => ScanEvent::REASON_OPERATOR_NOT_AUTHORIZED,
                    'details' => ['worker_id' => $worker->id, 'equipment_id' => $equipment->id],
                ];
            }
        }

        $result = empty($reasons) ? ScanEvent::RESULT_GREEN : ScanEvent::RESULT_RED;

        return new ScanResult(
            result: $result,
            subjectType: ScanEvent::SUBJECT_EQUIPMENT,
            subjectId: $equipment->id,
            reasons: $reasons,
            tokenType: ScanEvent::TOKEN_EQUIPMENT,
        );
    }

    /**
     * Resolve a worker by employee_id (manual fallback when QR fails). Subject
     * to the same checks as a QR scan, but the caller marks the resulting
     * ScanEvent as is_manual_entry.
     */
    public function verifyManualWorker(string $employeeId, array $context = []): ScanResult
    {
        $worker = Worker::where('employee_id', $employeeId)->first();
        if ($worker === null) {
            return new ScanResult(
                result: ScanEvent::RESULT_RED,
                subjectType: ScanEvent::SUBJECT_WORKER,
                subjectId: null,
                reasons: [['code' => ScanEvent::REASON_UNKNOWN_QR, 'details' => ['employee_id' => $employeeId]]],
                tokenType: ScanEvent::TOKEN_MANUAL,
            );
        }

        return $this->verifyWorker($worker, ScanEvent::TOKEN_MANUAL, $context);
    }

    private function verifyWorker(Worker $worker, string $tokenType, array $context): ScanResult
    {
        $reasons = [];

        // Induction status (the gate-induction check)
        $today = now()->startOfDay();
        $inductionOk = $worker->induction_status === Worker::INDUCTION_INDUCTED
            && $worker->induction_valid_until !== null
            && $worker->induction_valid_until->gte($today);
        if (! $inductionOk) {
            $reasons[] = [
                'code' => ScanEvent::REASON_INDUCTION_MISSING,
                'details' => [
                    'status' => $worker->induction_status,
                    'valid_until' => optional($worker->induction_valid_until)->toDateString(),
                ],
            ];
        }

        // Org engagement (only when project_id supplied)
        if (! empty($context['project_id'])) {
            $engaged = Engagement::where('project_id', $context['project_id'])
                ->where('organization_id', $worker->employer_organization_id)
                ->where('status', Engagement::STATUS_ACTIVE)
                ->exists();
            if (! $engaged) {
                $reasons[] = [
                    'code' => ScanEvent::REASON_ORG_NOT_ENGAGED,
                    'details' => [
                        'employer_organization_id' => $worker->employer_organization_id,
                        'project_id' => $context['project_id'],
                    ],
                ];
            }
        }

        // Medical
        $worker->loadMissing('latestMedicalRecord');
        $medical = $worker->latestMedicalRecord;
        if ($medical === null || ! $medical->isFit()) {
            $reasons[] = [
                'code' => ScanEvent::REASON_MEDICAL_FAIL,
                'details' => [
                    'status' => $medical?->status,
                    'valid_until' => optional($medical?->valid_until)->toDateString(),
                ],
            ];
        }

        // Expired certs (informational at gate; permit submit hard-blocks)
        $expired = $worker->certifications()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today->toDateString())
            ->with('certificationType')
            ->get();
        foreach ($expired as $cert) {
            $reasons[] = [
                'code' => ScanEvent::REASON_CERT_EXPIRED,
                'details' => [
                    'certification_type' => $cert->certificationType?->code,
                    'expired_on' => optional($cert->expiry_date)->toDateString(),
                ],
            ];
        }

        $result = empty($reasons) ? ScanEvent::RESULT_GREEN : ScanEvent::RESULT_RED;

        return new ScanResult(
            result: $result,
            subjectType: ScanEvent::SUBJECT_WORKER,
            subjectId: $worker->id,
            reasons: $reasons,
            tokenType: $tokenType,
        );
    }

    private function verifyEquipment(Equipment $equipment, array $context): ScanResult
    {
        $reasons = [];

        $equipment->loadMissing('latestCertification');
        $cert = $equipment->latestCertification;
        $tpiValid = $cert !== null && $cert->isValid();
        if (! $tpiValid) {
            $reasons[] = [
                'code' => ScanEvent::REASON_EQUIPMENT_TPI_EXPIRED,
                'details' => [
                    'last_inspection' => optional($cert?->inspection_date)->toDateString(),
                    'expiry_date' => optional($cert?->expiry_date)->toDateString(),
                    'result' => $cert?->result,
                ],
            ];
        }

        $result = empty($reasons) ? ScanEvent::RESULT_GREEN : ScanEvent::RESULT_RED;

        return new ScanResult(
            result: $result,
            subjectType: ScanEvent::SUBJECT_EQUIPMENT,
            subjectId: $equipment->id,
            reasons: $reasons,
            tokenType: ScanEvent::TOKEN_EQUIPMENT,
        );
    }
}
