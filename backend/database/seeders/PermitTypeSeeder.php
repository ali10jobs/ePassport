<?php

namespace Database\Seeders;

use App\Models\CertificationType;
use App\Models\PermitType;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER permit catalog. Each permit type is mapped to required
 * certifications for hard-block validation. Revise per PRE-1 review.
 */
class PermitTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedType(
            code: 'HOT_WORK',
            en: 'Hot Work Permit',
            ar: 'تصريح عمل ساخن',
            descEn: 'Welding, cutting, grinding, brazing, or any spark/flame-producing activity outside designated hot work zones.',
            descAr: 'اللحام والقطع والجلخ أو أي نشاط ينتج عنه شرر أو لهب خارج المناطق المخصصة.',
            validityHours: 8,
            consultant: true,
            gasTest: true,
            fireWatch: true,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS', 'WELDING_6G', 'FIRE_WATCH', 'GAS_TESTER'],
            requiredCertsByRole: [
                'WELDING_6G' => 'worker_only',
                'FIRE_WATCH' => 'worker_only',
                'GAS_TESTER' => 'supervisor_only',
            ],
        );

        $this->seedType(
            code: 'CONFINED_SPACE',
            en: 'Confined Space Entry Permit',
            ar: 'تصريح دخول الأماكن المغلقة',
            descEn: 'Entry into tanks, vessels, manholes, or any space with limited entry/exit and potential atmospheric hazard.',
            descAr: 'الدخول إلى الخزانات أو الأوعية أو الفتحات أو أي مكان ذي وصول محدود ومخاطر جوية محتملة.',
            validityHours: 8,
            consultant: true,
            gasTest: true,
            fireWatch: false,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS', 'CONFINED_ENTRY', 'ARAMCO_SAEP_55', 'GAS_TESTER'],
            requiredCertsByRole: [
                'GAS_TESTER' => 'supervisor_only',
            ],
        );

        $this->seedType(
            code: 'WORKING_AT_HEIGHTS',
            en: 'Working at Heights Permit',
            ar: 'تصريح العمل على الارتفاعات',
            descEn: 'Any work at >1.8m elevation including scaffold work, roof work, mast climbing, MEWP operation.',
            descAr: 'أي عمل على ارتفاع أكثر من 1.8 متر بما في ذلك السقالات وأعمال الأسطح ومنصات الرفع.',
            validityHours: 8,
            consultant: true,
            gasTest: false,
            fireWatch: false,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS', 'WAH_CERT', 'ARAMCO_SAEP_88'],
        );

        $this->seedType(
            code: 'EXCAVATION',
            en: 'Excavation Permit',
            ar: 'تصريح الحفر',
            descEn: 'Any excavation greater than 1m depth or in proximity to underground utilities.',
            descAr: 'أي حفر بعمق أكبر من متر واحد أو بالقرب من المرافق تحت الأرض.',
            validityHours: 12,
            consultant: true,
            gasTest: false,
            fireWatch: false,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS'],
        );

        $this->seedType(
            code: 'ELECTRICAL',
            en: 'Electrical Work Permit',
            ar: 'تصريح أعمال الكهرباء',
            descEn: 'Live electrical work, lockout/tagout activities, panel work above 50V.',
            descAr: 'أعمال كهربائية حية أو إجراءات الإغلاق/التعليم أو الأعمال على لوحات تتجاوز 50 فولت.',
            validityHours: 8,
            consultant: true,
            gasTest: false,
            fireWatch: false,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS', 'ELECTRICIAN_LV'],
            requiredCertsByRole: [
                'ELECTRICIAN_LV' => 'worker_only',
            ],
        );

        $this->seedType(
            code: 'LIFTING',
            en: 'Lifting / Crane Operations Permit',
            ar: 'تصريح أعمال الرفع / الرافعات',
            descEn: 'Any lifting operation using mobile or tower crane >5 tonnes or non-routine lifts.',
            descAr: 'أي عملية رفع باستخدام رافعة متحركة أو برجية بحمولة أكثر من 5 أطنان أو رفعات غير روتينية.',
            validityHours: 8,
            consultant: true,
            gasTest: false,
            fireWatch: false,
            requiredCerts: ['SITE_INDUCTION', 'MEDICAL_FITNESS', 'CRANE_OPERATOR', 'RIGGER_LVL2', 'BANKSMAN'],
            requiredCertsByRole: [
                'CRANE_OPERATOR' => 'worker_only',
                'RIGGER_LVL2' => 'worker_only',
                'BANKSMAN' => 'worker_only',
            ],
        );
    }

    /**
     * @param array<int, string> $requiredCerts
     * @param array<string, string> $requiredCertsByRole
     */
    private function seedType(
        string $code,
        string $en,
        string $ar,
        string $descEn,
        string $descAr,
        int $validityHours,
        bool $consultant,
        bool $gasTest,
        bool $fireWatch,
        array $requiredCerts,
        array $requiredCertsByRole = [],
    ): void {
        $type = PermitType::firstOrCreate(
            ['code' => $code],
            [
                'name_en' => $en,
                'name_ar' => $ar,
                'description_en' => $descEn,
                'description_ar' => $descAr,
                'default_validity_hours' => $validityHours,
                'requires_consultant_approval' => $consultant,
                'requires_gas_test' => $gasTest,
                'requires_fire_watch' => $fireWatch,
                'is_active' => true,
            ]
        );

        $sync = [];
        foreach ($requiredCerts as $certCode) {
            $cert = CertificationType::where('code', $certCode)->first();
            if ($cert === null) {
                continue;
            }
            $sync[$cert->id] = [
                'applies_to' => $requiredCertsByRole[$certCode] ?? 'all',
            ];
        }

        $type->requiredCertifications()->sync($sync);
    }
}
