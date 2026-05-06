<?php

namespace App\Services\Permit;

use App\Models\Equipment;
use App\Models\Permit;
use App\Models\Worker;

/**
 * Hard-block validation for permit submission. Re-runs all required checks
 * against the named workers and equipment AT SUBMIT TIME (data could have
 * changed since the permit was drafted — that's the whole point).
 *
 * Returns a structured result with per-worker and per-equipment failures.
 * The submit endpoint translates this into an HTTP 422 with stable codes.
 *
 * Validation rules:
 *   1. Each named worker has site induction valid AND medical fit.
 *   2. Each named worker has every required cert for the permit type, with
 *      role-aware applies_to (worker_only / supervisor_only / all).
 *      Required certs come from permit_type_required_certifications.
 *   3. Each named equipment has a valid TPI cert.
 *   4. Issuing organization is engaged on the project (ORG_NOT_ENGAGED otherwise).
 */
class PermitValidationService
{
    /**
     * @return array{ok: bool, worker_failures: array<int, array<string, mixed>>, equipment_failures: array<int, array<string, mixed>>, project_failures: array<int, array<string, mixed>>}
     */
    public function validateForSubmission(Permit $permit): array
    {
        $permit->loadMissing([
            'permitType.requiredCertifications',
            'workers.certifications.certificationType',
            'workers.medicalRecords',
            'workers' => fn ($q) => $q->withPivot('role_on_permit'),
            'equipment.latestCertification',
        ]);

        $workerFailures = [];
        $equipmentFailures = [];
        $projectFailures = [];
        $today = now()->startOfDay();

        // Required certs grouped by applies_to scope
        $required = $permit->permitType->requiredCertifications;
        $requiredAll = $required->filter(fn ($c) => ($c->pivot->applies_to ?? 'all') === 'all');
        $requiredWorkerOnly = $required->filter(fn ($c) => $c->pivot->applies_to === 'worker_only');
        $requiredSupervisorOnly = $required->filter(fn ($c) => $c->pivot->applies_to === 'supervisor_only');

        // ---- Workers ----
        foreach ($permit->workers as $worker) {
            $reasons = [];
            $role = $worker->pivot->role_on_permit ?? 'worker';

            // Induction
            $inductionOk = $worker->induction_status === Worker::INDUCTION_INDUCTED
                && $worker->induction_valid_until !== null
                && $worker->induction_valid_until->gte($today);
            if (! $inductionOk) {
                $reasons[] = [
                    'code' => 'INDUCTION_MISSING',
                    'details' => [
                        'status' => $worker->induction_status,
                        'valid_until' => optional($worker->induction_valid_until)->toDateString(),
                    ],
                ];
            }

            // Medical fitness (most recent record)
            $latestMedical = $worker->medicalRecords->sortByDesc('exam_date')->first();
            if ($latestMedical === null || ! $latestMedical->isFit()) {
                $reasons[] = [
                    'code' => 'MEDICAL_FAIL',
                    'details' => [
                        'status' => $latestMedical?->status,
                        'valid_until' => optional($latestMedical?->valid_until)->toDateString(),
                    ],
                ];
            }

            // Build per-worker required cert set based on role
            $needs = collect()
                ->merge($requiredAll)
                ->merge($role === 'supervisor' ? $requiredSupervisorOnly : collect())
                ->merge($role === 'worker' ? $requiredWorkerOnly : collect());

            $heldByCode = $worker->certifications->keyBy(fn ($c) => $c->certificationType?->code);

            foreach ($needs as $reqCert) {
                $held = $heldByCode->get($reqCert->code);
                if ($held === null) {
                    $reasons[] = [
                        'code' => 'CERT_MISSING',
                        'details' => [
                            'certification_type' => $reqCert->code,
                            'name_en' => $reqCert->name_en,
                            'role' => $role,
                        ],
                    ];
                    continue;
                }
                if ($held->expiry_date !== null && $held->expiry_date->lt($today)) {
                    $reasons[] = [
                        'code' => 'CERT_EXPIRED',
                        'details' => [
                            'certification_type' => $reqCert->code,
                            'expired_on' => $held->expiry_date->toDateString(),
                            'role' => $role,
                        ],
                    ];
                }
            }

            if (! empty($reasons)) {
                $workerFailures[] = [
                    'worker_id' => $worker->id,
                    'employee_id' => $worker->employee_id,
                    'full_name_en' => $worker->full_name_en,
                    'full_name_ar' => $worker->full_name_ar,
                    'role_on_permit' => $role,
                    'reasons' => $reasons,
                ];
            }
        }

        // ---- Equipment ----
        foreach ($permit->equipment as $eq) {
            $reasons = [];

            $cert = $eq->latestCertification;
            if ($cert === null || ! $cert->isValid()) {
                $reasons[] = [
                    'code' => 'EQUIPMENT_TPI_EXPIRED',
                    'details' => [
                        'last_inspection' => optional($cert?->inspection_date)->toDateString(),
                        'expiry_date' => optional($cert?->expiry_date)->toDateString(),
                        'result' => $cert?->result,
                    ],
                ];
            }

            if (! empty($reasons)) {
                $equipmentFailures[] = [
                    'equipment_id' => $eq->id,
                    'asset_tag' => $eq->asset_tag,
                    'manufacturer' => $eq->manufacturer,
                    'model' => $eq->model,
                    'reasons' => $reasons,
                ];
            }
        }

        // ---- Project / org engagement ----
        $engaged = $permit->issuing_organization_id !== null && $permit->project
            ?->engagements()
            ->where('organization_id', $permit->issuing_organization_id)
            ->where('status', 'active')
            ->exists();
        if (! $engaged) {
            $projectFailures[] = [
                'code' => 'ORG_NOT_ENGAGED',
                'details' => [
                    'issuing_organization_id' => $permit->issuing_organization_id,
                    'project_id' => $permit->project_id,
                ],
            ];
        }

        // Empty permits also can't submit
        if ($permit->workers->isEmpty()) {
            $projectFailures[] = [
                'code' => 'PERMIT_EMPTY_WORKERS',
                'details' => ['message' => 'Permit must have at least one named worker.'],
            ];
        }

        $ok = empty($workerFailures) && empty($equipmentFailures) && empty($projectFailures);

        return [
            'ok' => $ok,
            'worker_failures' => $workerFailures,
            'equipment_failures' => $equipmentFailures,
            'project_failures' => $projectFailures,
        ];
    }
}
