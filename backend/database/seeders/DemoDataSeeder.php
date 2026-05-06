<?php

namespace Database\Seeders;

use App\Models\CertificationType;
use App\Models\Engagement;
use App\Models\Equipment;
use App\Models\EquipmentCertification;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Site;
use App\Models\User;
use App\Models\UserOrganizationRole;
use App\Models\Worker;
use App\Models\WorkerCertification;
use App\Models\WorkerMedicalRecord;
use App\Services\QrCode\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Populates a single project with 4 orgs (one per role), 30 workers split
 * across the contractor and subcontractor, 10 equipment items. Some worker
 * certs are intentionally expired so the demo's red-scan moment lands.
 *
 * 3 user accounts — one per primary role — with password 'password'. These
 * are intended for local-dev / staging only; production seeds with real
 * accounts via a separate, env-gated seeder (post-MVP).
 *
 * Replays cleanly: uses firstOrCreate for catalog-anchored records and
 * checks for existing demo identifiers before creating.
 */
class DemoDataSeeder extends Seeder
{
    private QrCodeService $qr;

    public function run(): void
    {
        $this->qr = app(QrCodeService::class);

        // ---- Organizations ----
        $client = $this->org('AL-FUTAIM HOLDING (Demo Client)', 'الفطيم القابضة (عميل تجريبي)', Organization::ROLE_CLIENT, '1010001234');
        $mainContractor = $this->org('SALEH AL-RAJHI CONTRACTING (Demo)', 'مقاولات صالح الراجحي (تجريبي)', Organization::ROLE_MAIN_CONTRACTOR, '1010002345');
        $consultant = $this->org('SAUDI CONSULTING SERVICES (Demo)', 'الخدمات الاستشارية السعودية (تجريبي)', Organization::ROLE_CONSULTANT, '1010003456');
        $subcontractor = $this->org('AL-NAHDI SCAFFOLDING (Demo Sub)', 'النهدي للسقالات (مقاول من الباطن تجريبي)', Organization::ROLE_SUBCONTRACTOR, '1010004567');

        // ---- Project ----
        $project = Project::firstOrCreate(
            ['code' => 'DEMO-PROJ-2026-001'],
            [
                'client_organization_id' => $client->id,
                'name_en' => 'Riyadh Tower B - Structural Phase',
                'name_ar' => 'برج الرياض ب - مرحلة الهيكل الإنشائي',
                'description_en' => '40-storey commercial tower; structural steel + concrete works.',
                'description_ar' => 'برج تجاري من 40 طابقًا؛ أعمال الهيكل الفولاذي والخرساني.',
                'status' => Project::STATUS_ACTIVE,
                'start_date' => now()->subMonths(6)->toDateString(),
                'expected_end_date' => now()->addMonths(18)->toDateString(),
                'city' => 'Riyadh',
                'region' => 'Riyadh Province',
            ]
        );

        // ---- Engagements ----
        $mainEng = Engagement::firstOrCreate(
            [
                'project_id' => $project->id,
                'organization_id' => $mainContractor->id,
                'role' => Engagement::ROLE_MAIN_CONTRACTOR,
            ],
            [
                'scope_en' => 'Structural steel + concrete works',
                'scope_ar' => 'أعمال الهيكل الفولاذي والخرساني',
                'status' => Engagement::STATUS_ACTIVE,
                'start_date' => now()->subMonths(6)->toDateString(),
            ]
        );

        Engagement::firstOrCreate(
            [
                'project_id' => $project->id,
                'organization_id' => $consultant->id,
                'role' => Engagement::ROLE_CONSULTANT,
            ],
            [
                'scope_en' => 'HSE supervision and permit approval',
                'scope_ar' => 'الإشراف على السلامة والموافقة على التصاريح',
                'status' => Engagement::STATUS_ACTIVE,
                'start_date' => now()->subMonths(6)->toDateString(),
            ]
        );

        Engagement::firstOrCreate(
            [
                'project_id' => $project->id,
                'organization_id' => $subcontractor->id,
                'role' => Engagement::ROLE_SUBCONTRACTOR,
            ],
            [
                'parent_engagement_id' => $mainEng->id,
                'scope_en' => 'Scaffolding erection and dismantling',
                'scope_ar' => 'تركيب وتفكيك السقالات',
                'status' => Engagement::STATUS_ACTIVE,
                'start_date' => now()->subMonths(5)->toDateString(),
            ]
        );

        // ---- Site ----
        $site = Site::firstOrCreate(
            ['project_id' => $project->id, 'code' => 'GATE-A'],
            [
                'name_en' => 'Main Gate (North)',
                'name_ar' => 'البوابة الرئيسية (الشمالية)',
                'address_en' => 'King Fahd Road, Olaya, Riyadh',
                'address_ar' => 'طريق الملك فهد، العليا، الرياض',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'status' => 'active',
            ]
        );

        // ---- Demo users (one per primary role, password "password") ----
        $clientUser = $this->user('Sara Al-Saud', 'sara.client@epassport.local', $client, UserOrganizationRole::ROLE_CLIENT_SAFETY_LEAD);
        $contractorUser = $this->user('Khalid Al-Rajhi', 'khalid.maincon@epassport.local', $mainContractor, UserOrganizationRole::ROLE_HSE_MANAGER);
        $consultantUser = $this->user('Nasser Al-Otaibi', 'nasser.consultant@epassport.local', $consultant, UserOrganizationRole::ROLE_CONSULTANT);

        // ---- Workers ----
        $workers = $this->seedWorkers($mainContractor, $subcontractor);

        // ---- Equipment ----
        $this->seedEquipment($mainContractor);

        $this->command?->info('Demo data seeded.');
        $this->command?->info('  - 4 organizations, 1 project, 1 site, 3 engagements');
        $this->command?->info('  - 3 users (password "password"):');
        $this->command?->info('      sara.client@epassport.local      [client safety lead]');
        $this->command?->info('      khalid.maincon@epassport.local   [main contractor HSE manager]');
        $this->command?->info('      nasser.consultant@epassport.local [consultant]');
        $this->command?->info('  - '.count($workers).' workers (some certs intentionally expired)');
        $this->command?->info('  - 10 equipment items with TPI certs');
    }

    private function org(string $en, string $ar, string $role, string $cr): Organization
    {
        return Organization::firstOrCreate(
            ['commercial_registration' => $cr],
            [
                'name_en' => $en,
                'name_ar' => $ar,
                'default_role' => $role,
                'country' => 'SA',
                'contact_email' => str_replace(' ', '', 'contact@'.strtolower(explode(' ', $en)[0])).'.demo',
            ]
        );
    }

    private function user(string $name, string $email, Organization $org, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'locale' => 'en',
            ]
        );

        UserOrganizationRole::firstOrCreate(
            ['user_id' => $user->id, 'organization_id' => $org->id, 'role' => $role],
            ['is_default' => true]
        );

        return $user;
    }

    /**
     * @return array<int, Worker>
     */
    private function seedWorkers(Organization $mainContractor, Organization $subcontractor): array
    {
        // 25 main-contractor workers + 5 subcontractor workers = 30
        $roster = [
            // [employer_index, employee_id_seq, first_en, last_en, first_ar, last_ar, nationality, trade]
            [0, 'EMP-001', 'Mohammed', 'Al-Saud', 'محمد', 'السعود', 'SAU', 'Welder'],
            [0, 'EMP-002', 'Abdullah', 'Al-Qahtani', 'عبدالله', 'القحطاني', 'SAU', 'Site Supervisor'],
            [0, 'EMP-003', 'Fahad', 'Al-Otaibi', 'فهد', 'العتيبي', 'SAU', 'Foreman'],
            [0, 'EMP-004', 'Hassan', 'Khan', 'حسن', 'خان', 'PAK', 'Scaffolder'],
            [0, 'EMP-005', 'Imran', 'Ahmed', 'عمران', 'أحمد', 'PAK', 'Scaffolder'],
            [0, 'EMP-006', 'Suresh', 'Kumar', 'سوريش', 'كومار', 'IND', 'Welder'],
            [0, 'EMP-007', 'Rajesh', 'Patel', 'راجيش', 'باتيل', 'IND', 'Electrician'],
            [0, 'EMP-008', 'Mahesh', 'Reddy', 'ماهيش', 'ريدي', 'IND', 'Crane Operator'],
            [0, 'EMP-009', 'Ravi', 'Singh', 'رافي', 'سينغ', 'IND', 'Rigger'],
            [0, 'EMP-010', 'Mohammad', 'Rahman', 'محمد', 'الرحمن', 'BGD', 'Helper'],
            [0, 'EMP-011', 'Karim', 'Hossain', 'كريم', 'حسين', 'BGD', 'Helper'],
            [0, 'EMP-012', 'Juan', 'Reyes', 'خوان', 'رييس', 'PHL', 'Welder'],
            [0, 'EMP-013', 'Mark', 'Santos', 'مارك', 'سانتوس', 'PHL', 'Pipefitter'],
            [0, 'EMP-014', 'Roberto', 'Cruz', 'روبرتو', 'كروز', 'PHL', 'Crane Operator'],
            [0, 'EMP-015', 'Yousef', 'Al-Harbi', 'يوسف', 'الحربي', 'SAU', 'Safety Officer'],
            [0, 'EMP-016', 'Saad', 'Al-Mutairi', 'سعد', 'المطيري', 'SAU', 'Foreman'],
            [0, 'EMP-017', 'Bandar', 'Al-Shamri', 'بندر', 'الشمري', 'SAU', 'Banksman'],
            [0, 'EMP-018', 'Talal', 'Al-Dosari', 'طلال', 'الدوسري', 'SAU', 'Rigger'],
            [0, 'EMP-019', 'Ali', 'Al-Zahrani', 'علي', 'الزهراني', 'SAU', 'Electrician'],
            [0, 'EMP-020', 'Bilal', 'Akhtar', 'بلال', 'أختر', 'PAK', 'Welder'],
            [0, 'EMP-021', 'Asif', 'Mahmood', 'آصف', 'محمود', 'PAK', 'Pipefitter'],
            [0, 'EMP-022', 'Vikram', 'Sharma', 'فيكرام', 'شارما', 'IND', 'Helper'],
            [0, 'EMP-023', 'Sunil', 'Verma', 'سونيل', 'فيرما', 'IND', 'Helper'],
            [0, 'EMP-024', 'Cesar', 'Garcia', 'سيزار', 'غارسيا', 'PHL', 'Scaffolder'],
            [0, 'EMP-025', 'Anwar', 'Hossain', 'أنور', 'حسين', 'BGD', 'Helper'],
            // Subcontractor workers
            [1, 'SUB-001', 'Tariq', 'Hussain', 'طارق', 'حسين', 'PAK', 'Scaffolder'],
            [1, 'SUB-002', 'Rizwan', 'Malik', 'رضوان', 'مالك', 'PAK', 'Scaffolder'],
            [1, 'SUB-003', 'Ehsan', 'Ali', 'إحسان', 'علي', 'PAK', 'Scaffolder Foreman'],
            [1, 'SUB-004', 'Faisal', 'Iqbal', 'فيصل', 'إقبال', 'PAK', 'Scaffolder'],
            [1, 'SUB-005', 'Nadeem', 'Aslam', 'نديم', 'أسلم', 'PAK', 'Scaffolder'],
        ];

        $employers = [$mainContractor, $subcontractor];
        $created = [];

        foreach ($roster as $i => $row) {
            [$employerIdx, $empId, $fEn, $lEn, $fAr, $lAr, $nat, $trade] = $row;

            $worker = Worker::firstOrCreate(
                ['employer_organization_id' => $employers[$employerIdx]->id, 'employee_id' => $empId],
                [
                    'first_name_en' => $fEn,
                    'last_name_en' => $lEn,
                    'first_name_ar' => $fAr,
                    'last_name_ar' => $lAr,
                    'nationality' => $nat,
                    'trade' => $trade,
                    'iqama_number' => $nat !== 'SAU' ? '23'.str_pad((string) (10000000 + $i * 17), 8, '0', STR_PAD_LEFT) : null,
                    'national_id' => $nat === 'SAU' ? '10'.str_pad((string) (10000000 + $i * 31), 8, '0', STR_PAD_LEFT) : null,
                    'induction_status' => $i % 11 === 5 ? Worker::INDUCTION_NOT_INDUCTED : Worker::INDUCTION_INDUCTED,
                    'induction_date' => $i % 11 === 5 ? null : now()->subDays($i * 2 + 30)->toDateString(),
                    'induction_valid_until' => $i % 11 === 5 ? null : now()->addMonths(11 - ($i % 4))->toDateString(),
                    'helmet_qr_token' => $this->qr->generateToken(),
                    'coverall_qr_token' => $this->qr->generateToken(),
                ]
            );
            $created[] = $worker;

            $this->seedWorkerCerts($worker, $i);
            $this->seedWorkerMedical($worker, $i);
        }

        return $created;
    }

    private function seedWorkerCerts(Worker $worker, int $index): void
    {
        // All workers get site induction (always) and medical fitness; the
        // distribution of trade certs varies + every Nth worker has an
        // intentionally expired cert to drive the demo's red-scan moment.

        $expiredEvery = 7; // 1 in 7 workers has an expired trade cert
        $isExpired = ($index % $expiredEvery === 3);

        // Always: site induction (matches induction_date)
        $induction = CertificationType::where('code', 'SITE_INDUCTION')->first();
        if ($induction !== null && $worker->induction_date !== null) {
            WorkerCertification::firstOrCreate(
                ['worker_id' => $worker->id, 'certification_type_id' => $induction->id],
                [
                    'issuing_body_en' => 'Riyadh Tower B Site HSE Team',
                    'issuing_body_ar' => 'فريق السلامة بالموقع - برج الرياض ب',
                    'issue_date' => $worker->induction_date,
                    'expiry_date' => $worker->induction_valid_until,
                    'verified' => true,
                    'verified_at' => now(),
                ]
            );
        }

        // Trade-specific cert based on stated trade
        $tradeMap = [
            'Welder' => 'WELDING_6G',
            'Scaffolder' => 'SCAFFOLD_BASIC',
            'Scaffolder Foreman' => 'SCAFFOLD_ADV',
            'Electrician' => 'ELECTRICIAN_LV',
            'Crane Operator' => 'CRANE_OPERATOR',
            'Rigger' => 'RIGGER_LVL2',
            'Banksman' => 'BANKSMAN',
        ];
        if (isset($tradeMap[$worker->trade])) {
            $tradeCert = CertificationType::where('code', $tradeMap[$worker->trade])->first();
            if ($tradeCert !== null) {
                WorkerCertification::firstOrCreate(
                    ['worker_id' => $worker->id, 'certification_type_id' => $tradeCert->id],
                    [
                        'issuing_body_en' => $tradeCert->default_issuing_body_en,
                        'issuing_body_ar' => $tradeCert->default_issuing_body_ar,
                        'issue_date' => now()->subYears(2)->subDays($index)->toDateString(),
                        'expiry_date' => $isExpired
                            ? now()->subDays(15 + $index)->toDateString()  // EXPIRED
                            : now()->addYear()->subDays($index)->toDateString(),
                        'verified' => true,
                        'verified_at' => now()->subYears(2)->subDays($index),
                    ]
                );
            }
        }

        // Working at heights for scaffolders
        if (str_contains($worker->trade, 'Scaffolder')) {
            $wah = CertificationType::where('code', 'WAH_CERT')->first();
            if ($wah !== null) {
                WorkerCertification::firstOrCreate(
                    ['worker_id' => $worker->id, 'certification_type_id' => $wah->id],
                    [
                        'issuing_body_en' => 'Approved local provider',
                        'issuing_body_ar' => 'مزود محلي معتمد',
                        'issue_date' => now()->subYear()->subDays($index)->toDateString(),
                        'expiry_date' => now()->addYear()->toDateString(),
                        'verified' => true,
                    ]
                );
            }
        }

        // NEBOSH / IOSH for supervisors and foremen
        if (str_contains($worker->trade, 'Supervisor') || str_contains($worker->trade, 'Foreman') || $worker->trade === 'Safety Officer') {
            $iosh = CertificationType::where('code', 'IOSH_MS')->first();
            if ($iosh !== null) {
                WorkerCertification::firstOrCreate(
                    ['worker_id' => $worker->id, 'certification_type_id' => $iosh->id],
                    [
                        'issuing_body_en' => 'IOSH',
                        'issuing_body_ar' => 'إيوش',
                        'issue_date' => now()->subYears(2)->toDateString(),
                        'expiry_date' => now()->addYear()->toDateString(),
                        'verified' => true,
                    ]
                );
            }
        }
    }

    private function seedWorkerMedical(Worker $worker, int $index): void
    {
        $medical = CertificationType::where('code', 'MEDICAL_FITNESS')->first();
        if ($medical === null) {
            return;
        }

        // 1 in 13 workers has an unfit medical to drive a MEDICAL_FAIL demo
        $unfit = ($index % 13 === 7);

        WorkerMedicalRecord::firstOrCreate(
            ['worker_id' => $worker->id, 'exam_date' => now()->subMonths(2 + ($index % 6))->toDateString()],
            [
                'valid_until' => now()->addMonths(10 - ($index % 5))->toDateString(),
                'status' => $unfit ? WorkerMedicalRecord::STATUS_UNFIT : WorkerMedicalRecord::STATUS_FIT,
                'examining_clinic_en' => 'Saudi German Hospital, Riyadh',
                'examining_clinic_ar' => 'المستشفى السعودي الألماني، الرياض',
                'restrictions_en' => $unfit ? 'No work at heights pending review' : null,
                'restrictions_ar' => $unfit ? 'يمنع العمل على الارتفاعات حتى المراجعة' : null,
            ]
        );
    }

    private function seedEquipment(Organization $owner): void
    {
        $items = [
            ['CR-001', 'crane', 'mobile_crane', 'Liebherr', 'LTM 1090-4.2', 90000.0],
            ['CR-002', 'crane', 'tower_crane', 'Potain', 'MDT 219', 8000.0],
            ['CR-003', 'crane', 'mobile_crane', 'Tadano', 'GR-300EX-3', 30000.0],
            ['SC-001', 'scaffold', 'system_scaffold', 'Layher', 'Allround LW', 200.0],
            ['SC-002', 'scaffold', 'system_scaffold', 'Layher', 'Allround LW', 200.0],
            ['MB-001', 'man_basket', 'cherry_picker', 'JLG', '600AJ', 230.0],
            ['MB-002', 'man_basket', 'scissor_lift', 'Genie', 'GS-3246', 318.0],
            ['LG-001', 'lifting_gear', 'wire_rope_sling', 'Crosby', 'A-1339', 5000.0],
            ['GN-001', 'generator', 'diesel_500kva', 'Caterpillar', 'C15', null],
            ['WR-001', 'welding_rig', 'arc_welder', 'Lincoln', 'Vantage 575', null],
        ];

        foreach ($items as $i => [$tag, $type, $category, $manufacturer, $model, $swl]) {
            $eq = Equipment::firstOrCreate(
                ['owner_organization_id' => $owner->id, 'asset_tag' => $tag],
                [
                    'serial_number' => 'SN-'.strtoupper(bin2hex(random_bytes(4))),
                    'manufacturer' => $manufacturer,
                    'model' => $model,
                    'type' => $type,
                    'category' => $category,
                    'manufacture_date' => now()->subYears(3 + ($i % 4))->toDateString(),
                    'safe_working_load_kg' => $swl,
                    'qr_token' => $this->qr->generateToken(),
                ]
            );

            // 1 in 5 equipment has expired TPI for the demo
            $isExpired = ($i % 5 === 2);
            $tpiBodies = ['TÜV Rheinland', 'Bureau Veritas', 'SGS', 'Lloyd\'s Register'];

            EquipmentCertification::firstOrCreate(
                ['equipment_id' => $eq->id, 'inspection_date' => now()->subMonths(6 + ($i % 4))->toDateString()],
                [
                    'certificate_number' => 'TPI-'.now()->year.'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'inspection_type' => 'periodic',
                    'tpi_body_en' => $tpiBodies[$i % 4],
                    'tpi_body_ar' => $tpiBodies[$i % 4], // Arabic translation deferred to PRE-1
                    'inspector_name' => 'Authorised Inspector',
                    'expiry_date' => $isExpired
                        ? now()->subDays(20 + $i)->toDateString()
                        : now()->addMonths(6 - ($i % 4))->toDateString(),
                    'result' => 'pass',
                ]
            );
        }
    }
}
