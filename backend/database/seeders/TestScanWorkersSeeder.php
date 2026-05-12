<?php

namespace Database\Seeders;

use App\Models\CertificationType;
use App\Models\Organization;
use App\Models\Worker;
use App\Models\WorkerCertification;
use App\Models\WorkerMedicalRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Test workers for manual gate-scan demos. Three workers under the main
 * contractor with predictable employee_ids the tester can type into the
 * "Enter manually" sheet:
 *
 *   9000000001 — induction valid + certs + medical → GREEN
 *   9000000002 — induction expired yesterday + certs + medical → RED
 *   9000000003 — never inducted, medical only (no certs) → RED
 *
 * IDs match the Saudi national/iqama format the gate clerk will be typing
 * (10 digits, leading 9 for resident ID range).
 *
 * All three carry a FIT medical record + blood type / emergency contact so
 * the Medical tab on the scan result screen has something to render.
 * Idempotent: re-running updates state but doesn't duplicate.
 */
class TestScanWorkersSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('default_role', Organization::ROLE_MAIN_CONTRACTOR)->first();
        if ($org === null) {
            $this->command?->warn('No main-contractor org seeded yet — run DemoDataSeeder first.');

            return;
        }

        $today = now()->startOfDay();

        $specs = [
            [
                'employee_id' => '9000000001',
                'first_name_en' => 'Ahmed', 'last_name_en' => 'Valid',
                'first_name_ar' => 'أحمد', 'last_name_ar' => 'صالح',
                'induction_status' => Worker::INDUCTION_INDUCTED,
                'induction_date' => $today->copy()->subMonths(2)->toDateString(),
                'induction_valid_until' => $today->copy()->addMonths(10)->toDateString(),
                'blood_type' => 'O+',
                'allergies' => 'None',
                'chronic_conditions' => null,
                'emergency_contact_name' => 'Fatimah Al-Valid',
                'emergency_contact_phone' => '+966500000001',
                'certs' => [
                    ['code' => 'NEBOSH_IGC', 'issued_months_ago' => 6, 'valid_months' => 60],
                    ['code' => 'WAH_CERT',   'issued_months_ago' => 3, 'valid_months' => 24],
                    ['code' => 'FIRST_AID',  'issued_months_ago' => 4, 'valid_months' => 24],
                ],
            ],
            [
                'employee_id' => '9000000002',
                'first_name_en' => 'Khalid', 'last_name_en' => 'Expired',
                'first_name_ar' => 'خالد', 'last_name_ar' => 'منتهي',
                'induction_status' => Worker::INDUCTION_INDUCTED,
                'induction_date' => $today->copy()->subYears(1)->subDays(5)->toDateString(),
                'induction_valid_until' => $today->copy()->subDays(1)->toDateString(),
                'blood_type' => 'A-',
                'allergies' => 'Penicillin',
                'chronic_conditions' => 'Asthma (well-controlled)',
                'emergency_contact_name' => 'Sara Al-Expired',
                'emergency_contact_phone' => '+966500000002',
                'certs' => [
                    ['code' => 'OSHA_30',         'issued_months_ago' => 18, 'valid_months' => 60],
                    ['code' => 'SCAFFOLD_BASIC',  'issued_months_ago' => 8,  'valid_months' => 36],
                ],
            ],
            [
                'employee_id' => '9000000003',
                'first_name_en' => 'Omar', 'last_name_en' => 'NoInduction',
                'first_name_ar' => 'عمر', 'last_name_ar' => 'بلاتدريب',
                'induction_status' => Worker::INDUCTION_NOT_INDUCTED,
                'induction_date' => null,
                'induction_valid_until' => null,
                'blood_type' => 'B+',
                'allergies' => 'Latex',
                'chronic_conditions' => null,
                'emergency_contact_name' => 'Layla Al-NoIndu',
                'emergency_contact_phone' => '+966500000003',
                'certs' => [], // Intentionally empty — Certifications tab should say "None"
            ],
        ];

        foreach ($specs as $spec) {
            $worker = Worker::firstOrCreate(
                ['employee_id' => $spec['employee_id']],
                [
                    'employer_organization_id' => $org->id,
                    'first_name_en' => $spec['first_name_en'],
                    'last_name_en' => $spec['last_name_en'],
                    'first_name_ar' => $spec['first_name_ar'],
                    'last_name_ar' => $spec['last_name_ar'],
                    'nationality' => 'SA',
                    'trade' => 'general_worker',
                    'induction_status' => $spec['induction_status'],
                    'induction_date' => $spec['induction_date'],
                    'induction_valid_until' => $spec['induction_valid_until'],
                    'helmet_qr_token' => 'test-helmet-'.Str::uuid(),
                    'coverall_qr_token' => 'test-coverall-'.Str::uuid(),
                ]
            );

            // Force-update state on re-run so the seeder can flip values
            // without wiping rows by hand.
            $worker->forceFill([
                'employer_organization_id' => $org->id,
                'induction_status' => $spec['induction_status'],
                'induction_date' => $spec['induction_date'],
                'induction_valid_until' => $spec['induction_valid_until'],
                'blood_type' => $spec['blood_type'],
                'allergies' => $spec['allergies'],
                'chronic_conditions' => $spec['chronic_conditions'],
                'emergency_contact_name' => $spec['emergency_contact_name'],
                'emergency_contact_phone' => $spec['emergency_contact_phone'],
            ])->save();

            // FIT medical so induction is the only failing dimension
            WorkerMedicalRecord::updateOrCreate(
                ['worker_id' => $worker->id, 'exam_date' => $today->copy()->subMonths(2)->toDateString()],
                [
                    'valid_until' => $today->copy()->addMonths(10)->toDateString(),
                    'status' => WorkerMedicalRecord::STATUS_FIT,
                    'examining_clinic_en' => 'Saudi German Hospital, Riyadh',
                    'examining_clinic_ar' => 'المستشفى السعودي الألماني، الرياض',
                ]
            );

            // Wipe & re-seed certifications so the seeder is fully idempotent.
            $worker->certifications()->delete();
            foreach ($spec['certs'] as $cert) {
                $type = CertificationType::where('code', $cert['code'])->first();
                if ($type === null) {
                    $this->command?->warn("  - cert type {$cert['code']} not found; skipping");
                    continue;
                }
                $issued = $today->copy()->subMonths($cert['issued_months_ago']);
                $expiry = $issued->copy()->addMonths($cert['valid_months']);
                WorkerCertification::create([
                    'worker_id' => $worker->id,
                    'certification_type_id' => $type->id,
                    'certificate_number' => 'TEST-'.strtoupper(Str::random(6)),
                    'issuing_body_en' => $type->body_en ?? 'Approved provider',
                    'issuing_body_ar' => $type->body_ar ?? 'مزود معتمد',
                    'issue_date' => $issued->toDateString(),
                    'expiry_date' => $expiry->toDateString(),
                    'verified' => true,
                    'verified_at' => now(),
                ]);
            }
        }

        $this->command?->info('Seeded 3 manual-scan test workers (with certs + medical profile).');
    }
}
