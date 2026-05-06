<?php

namespace Database\Seeders;

use App\Models\CertificationType;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER catalog of Saudi-context certifications. Names and bodies
 * captured from PRE-1 friend review will replace these strings post-demo.
 *
 * Naming convention: code is UPPER_SNAKE_CASE and stable. Names can change.
 */
class CertificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Safety training
            ['code' => 'NEBOSH_IGC', 'cat' => 'safety_training', 'en' => 'NEBOSH International General Certificate', 'ar' => 'شهادة نيبوش الدولية العامة', 'body_en' => 'NEBOSH', 'body_ar' => 'نيبوش', 'months' => 60],
            ['code' => 'IOSH_MS', 'cat' => 'safety_training', 'en' => 'IOSH Managing Safely', 'ar' => 'إدارة السلامة من إيوش', 'body_en' => 'IOSH', 'body_ar' => 'إيوش', 'months' => 36],
            ['code' => 'OSHA_30', 'cat' => 'safety_training', 'en' => 'OSHA 30-Hour Construction', 'ar' => 'شهادة أوشا 30 ساعة للإنشاءات', 'body_en' => 'OSHA', 'body_ar' => 'أوشا', 'months' => 60],
            ['code' => 'FIRST_AID', 'cat' => 'safety_training', 'en' => 'First Aid & CPR', 'ar' => 'الإسعافات الأولية وإنعاش القلب', 'body_en' => 'Saudi Red Crescent / equivalent', 'body_ar' => 'الهلال الأحمر السعودي أو ما يعادله', 'months' => 24],
            ['code' => 'FIRE_WATCH', 'cat' => 'safety_training', 'en' => 'Fire Watch Certification', 'ar' => 'شهادة مراقب الحريق', 'body_en' => 'Approved local provider', 'body_ar' => 'مزود محلي معتمد', 'months' => 12],

            // Aramco-specific safety competency programs (placeholders; revise post-PRE-1)
            ['code' => 'ARAMCO_SAEP_55', 'cat' => 'safety_training', 'en' => 'Aramco SAEP-55 Confined Space', 'ar' => 'أرامكو SAEP-55 الأماكن المغلقة', 'body_en' => 'Aramco-approved trainer', 'body_ar' => 'مدرب معتمد من أرامكو', 'months' => 24],
            ['code' => 'ARAMCO_SAEP_88', 'cat' => 'safety_training', 'en' => 'Aramco SAEP-88 Working at Heights', 'ar' => 'أرامكو SAEP-88 العمل على الارتفاعات', 'body_en' => 'Aramco-approved trainer', 'body_ar' => 'مدرب معتمد من أرامكو', 'months' => 24],

            // Trade competency
            ['code' => 'SCAFFOLD_BASIC', 'cat' => 'trade_competency', 'en' => 'Basic Scaffolding (Erection)', 'ar' => 'تركيب السقالات - أساسي', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 36],
            ['code' => 'SCAFFOLD_ADV', 'cat' => 'trade_competency', 'en' => 'Advanced Scaffolding (Inspection)', 'ar' => 'فحص السقالات - متقدم', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 36],
            ['code' => 'WAH_CERT', 'cat' => 'trade_competency', 'en' => 'Working at Heights', 'ar' => 'العمل على الارتفاعات', 'body_en' => 'Approved local provider', 'body_ar' => 'مزود محلي معتمد', 'months' => 24],
            ['code' => 'CONFINED_ENTRY', 'cat' => 'trade_competency', 'en' => 'Confined Space Entry', 'ar' => 'دخول الأماكن المغلقة', 'body_en' => 'Approved local provider', 'body_ar' => 'مزود محلي معتمد', 'months' => 12],
            ['code' => 'GAS_TESTER', 'cat' => 'trade_competency', 'en' => 'Authorised Gas Tester', 'ar' => 'فاحص غاز معتمد', 'body_en' => 'Approved local provider', 'body_ar' => 'مزود محلي معتمد', 'months' => 24],
            ['code' => 'RIGGER_LVL2', 'cat' => 'trade_competency', 'en' => 'Rigger Level 2', 'ar' => 'منزّق مستوى 2', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 36],
            ['code' => 'BANKSMAN', 'cat' => 'trade_competency', 'en' => 'Banksman / Signaller', 'ar' => 'موجه الرافعات', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 36],
            ['code' => 'CRANE_OPERATOR', 'cat' => 'trade_competency', 'en' => 'Mobile Crane Operator', 'ar' => 'مشغل رافعة متحركة', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 36],
            ['code' => 'WELDING_6G', 'cat' => 'trade_competency', 'en' => 'Welding 6G (SMAW/GTAW)', 'ar' => 'لحام 6G', 'body_en' => 'TÜV / Bureau Veritas / SGS', 'body_ar' => 'TÜV / Bureau Veritas / SGS', 'months' => 24],
            ['code' => 'ELECTRICIAN_LV', 'cat' => 'trade_competency', 'en' => 'Low Voltage Electrician', 'ar' => 'كهربائي جهد منخفض', 'body_en' => 'Approved local provider', 'body_ar' => 'مزود محلي معتمد', 'months' => 36],

            // Site induction (per-project; tracked centrally for the demo)
            ['code' => 'SITE_INDUCTION', 'cat' => 'site_induction', 'en' => 'General Site Induction', 'ar' => 'تعريف الموقع العام', 'body_en' => 'Site HSE Team', 'body_ar' => 'فريق السلامة بالموقع', 'months' => 12],

            // Medical
            ['code' => 'MEDICAL_FITNESS', 'cat' => 'medical', 'en' => 'Medical Fitness Certificate', 'ar' => 'شهادة لياقة طبية', 'body_en' => 'Approved clinic', 'body_ar' => 'عيادة معتمدة', 'months' => 12],
        ];

        foreach ($types as $t) {
            CertificationType::firstOrCreate(
                ['code' => $t['code']],
                [
                    'name_en' => $t['en'],
                    'name_ar' => $t['ar'],
                    'category' => $t['cat'],
                    'default_issuing_body_en' => $t['body_en'],
                    'default_issuing_body_ar' => $t['body_ar'],
                    'typical_validity_months' => $t['months'],
                    'requires_document_upload' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
