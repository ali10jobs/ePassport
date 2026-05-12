<?php

namespace App\Services\Worker;

use App\Models\Worker;
use App\Services\QrCode\QrCodeService;

class WorkerService
{
    public function __construct(private readonly QrCodeService $qrCodes) {}

    /**
     * Create a worker. Helmet and coverall QR tokens are auto-generated; the
     * caller never supplies them — they're random per-worker secrets used only
     * by the gate-scan flow. Tokens are unique because of the schema constraint;
     * we retry on the (extremely unlikely) collision.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Worker
    {
        $data['helmet_qr_token'] = $this->qrCodes->generateToken();
        $data['coverall_qr_token'] = $this->qrCodes->generateToken();

        return Worker::create($data);
    }

    /**
     * Rotate a worker's QR tokens. Used post-MVP when a helmet/coverall is
     * lost or stolen and old tokens must be invalidated. v1.0 does not
     * expose this; reserved here so the service contract is stable.
     */
    public function rotateTokens(Worker $worker): Worker
    {
        $worker->update([
            'helmet_qr_token' => $this->qrCodes->generateToken(),
            'coverall_qr_token' => $this->qrCodes->generateToken(),
        ]);

        return $worker->fresh();
    }

    /**
     * Build the consolidated e-Passport view returned by GET /workers/{id}/passport.
     * This is the read model the gate-scan UI and the worker detail screen
     * project from.
     *
     * @return array<string, mixed>
     */
    public function passport(Worker $worker): array
    {
        $worker->loadMissing([
            'employerOrganization',
            'certifications.certificationType',
            'latestMedicalRecord',
        ]);

        $certs = $worker->certifications->map(fn ($cert) => [
            'id' => $cert->id,
            'type_code' => $cert->certificationType?->code,
            'type_name_en' => $cert->certificationType?->name_en,
            'type_name_ar' => $cert->certificationType?->name_ar,
            'category' => $cert->certificationType?->category,
            'certificate_number' => $cert->certificate_number,
            'issuing_body_en' => $cert->issuing_body_en,
            'issuing_body_ar' => $cert->issuing_body_ar,
            'issue_date' => optional($cert->issue_date)->toDateString(),
            'expiry_date' => optional($cert->expiry_date)->toDateString(),
            'status' => $cert->status,
            'verified' => (bool) $cert->verified,
        ])->all();

        $medical = $worker->latestMedicalRecord ? [
            'id' => $worker->latestMedicalRecord->id,
            'exam_date' => optional($worker->latestMedicalRecord->exam_date)->toDateString(),
            'valid_until' => optional($worker->latestMedicalRecord->valid_until)->toDateString(),
            'status' => $worker->latestMedicalRecord->status,
            'is_currently_fit' => $worker->latestMedicalRecord->isFit(),
        ] : null;

        return [
            'id' => $worker->id,
            'employee_id' => $worker->employee_id,
            'full_name_en' => $worker->full_name_en,
            'full_name_ar' => $worker->full_name_ar,
            'nationality' => $worker->nationality,
            'trade' => $worker->trade,
            'employer' => [
                'id' => $worker->employerOrganization?->id,
                'name_en' => $worker->employerOrganization?->name_en,
                'name_ar' => $worker->employerOrganization?->name_ar,
            ],
            'induction' => [
                'status' => $worker->induction_status,
                'date' => optional($worker->induction_date)->toDateString(),
                'valid_until' => optional($worker->induction_valid_until)->toDateString(),
            ],
            'medical_fitness' => $medical,
            'medical_profile' => [
                'blood_type' => $worker->blood_type,
                'allergies' => $worker->allergies,
                'chronic_conditions' => $worker->chronic_conditions,
                'emergency_contact_name' => $worker->emergency_contact_name,
                'emergency_contact_phone' => $worker->emergency_contact_phone,
            ],
            'certifications' => $certs,
            'photo_path' => $worker->photo_path,
        ];
    }
}
