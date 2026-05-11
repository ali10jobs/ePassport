// Lightweight EN/AR string table covering every visible label across
// the app. Full .arb-based flutter_gen pipeline is a follow-up; this
// keeps the language toggle working today.
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'preferences.dart';

class AppStrings {
  final Locale locale;
  const AppStrings(this.locale);

  bool get isAr => locale.languageCode == 'ar';
  String _t(String en, String ar) => isAr ? ar : en;

  // Sidebar / Profile / Account / Settings
  String get profile => _t('Profile', 'الملف الشخصي');
  String get account => _t('Account', 'الحساب');
  String get settings => _t('Settings', 'الإعدادات');
  String get signOut => _t('Sign out', 'تسجيل الخروج');
  String get language => _t('Language', 'اللغة');
  String get appearance => _t('Appearance', 'المظهر');
  String get light => _t('Light', 'فاتح');
  String get dark => _t('Dark', 'داكن');
  String get system => _t('System', 'النظام');
  String get english => _t('English', 'الإنجليزية');
  String get arabic => _t('Arabic', 'العربية');
  String get security => _t('Security', 'الأمان');
  String get privacy => _t('Privacy', 'الخصوصية');
  String get passwordReset => _t('Password reset', 'إعادة تعيين كلمة المرور');
  String get changePhoto => _t('Change photo', 'تغيير الصورة');
  String get bio => _t('Bio', 'النبذة');
  String get save => _t('Save', 'حفظ');
  String get cancel => _t('Cancel', 'إلغاء');
  String get name => _t('Name', 'الاسم');
  String get jobTitle => _t('Job title', 'المسمى الوظيفي');
  String get email => _t('Email', 'البريد الإلكتروني');
  String get preferences => _t('PREFERENCES', 'التفضيلات');
  String get securitySubtitle => _t(
        'Two-factor auth, active sessions, trusted devices.',
        'المصادقة الثنائية، الجلسات النشطة، الأجهزة الموثوقة.',
      );
  String get privacySubtitle => _t(
        'Data sharing, audit log visibility, exports.',
        'مشاركة البيانات، رؤية سجل التدقيق، التصدير.',
      );
  String get passwordResetSubtitle => _t(
        'Send a reset link to your registered email.',
        'إرسال رابط إعادة التعيين إلى بريدك الإلكتروني المسجل.',
      );
  String get sendResetLink =>
      _t('Send password reset link?', 'إرسال رابط إعادة تعيين كلمة المرور؟');
  String resetLinkBody(String email) => isAr
      ? (email.isEmpty
          ? 'سيتم إرسال رابط إعادة التعيين إلى بريدك المسجل.'
          : 'سيتم إرسال رابط إعادة التعيين إلى $email.')
      : (email.isEmpty
          ? 'A reset link will be sent to your registered email.'
          : 'A reset link will be sent to $email.');
  String get send => _t('Send', 'إرسال');
  String get resetLinkSent => _t('Reset link sent.', 'تم إرسال رابط إعادة التعيين.');
  String get comingSoon => _t('coming soon.', 'قريباً.');
  String get signOutPrompt => _t(
        'You will need to sign in again to access your tools.',
        'ستحتاج إلى تسجيل الدخول مرة أخرى للوصول إلى أدواتك.',
      );
  String get profileSaved => _t('Profile saved', 'تم حفظ الملف الشخصي');
  String get bioHint =>
      _t('Tell your team about your role…', 'أخبر فريقك عن دورك…');

  // Launch screen
  String get appTitle => 'ePassport';
  String get launchSubtitle => _t(
        'Securing industrial zones through intelligent permit management.',
        'تأمين المناطق الصناعية من خلال إدارة التصاريح الذكية.',
      );
  String get login => _t('Login', 'تسجيل الدخول');
  String get reportHazardAnonymously =>
      _t('Report Hazard Anonymously', 'الإبلاغ عن خطر بشكل مجهول');
  String get launchFooterBlurb => _t(
        'Scanning hardware and personnel credentials\nfor site access authorization.',
        'مسح بيانات الأجهزة وبيانات اعتماد الموظفين\nلتفويض الوصول إلى الموقع.',
      );
  String get encryptionActive =>
      _t('ENCRYPTION ACTIVE AES-256', 'التشفير نشط AES-256');

  // Login screen
  String get systemAccess => _t('System Access', 'الوصول إلى النظام');
  String get emailLabel => _t('EMAIL', 'البريد الإلكتروني');
  String get passwordLabel => _t('PASSWORD', 'كلمة المرور');
  String get signIn => _t('SIGN IN', 'تسجيل الدخول');
  String get secureSession => _t('Secure Session', 'جلسة آمنة');
  String get secureSessionBlurb => _t(
        'All credentials are encrypted end-to-end. Session expires after 30 minutes of inactivity.',
        'جميع بيانات الاعتماد مشفرة من طرف إلى طرف. تنتهي الجلسة بعد 30 دقيقة من عدم النشاط.',
      );
  String get termsAcknowledgement => _t(
        'By signing in you accept our Terms of Service.',
        'بتسجيل الدخول، فإنك تقبل شروط الخدمة الخاصة بنا.',
      );
  String get loginFailed => _t('Could not sign in.', 'تعذر تسجيل الدخول.');

  // Dashboard
  String get engineerPortal => _t('ENGINEER PORTAL', 'بوابة المهندس');
  String welcomeBack(String name) =>
      isAr ? 'مرحباً بعودتك، $name' : 'Welcome back, $name';
  String get recentScans => _t('RECENT SCANS', 'عمليات المسح الأخيرة');
  String get today => _t('TODAY', 'اليوم');
  String get pendingPermits => _t('PENDING PERMITS', 'التصاريح المعلقة');
  String get actionRequired => _t('ACTION REQUIRED', 'إجراء مطلوب');
  String get organization => _t('ORGANIZATION', 'المنظمة');
  String get myPermits => _t('MY PERMITS', 'تصاريحي');
  String get hazardReports => _t('HAZARD REPORTS', 'بلاغات المخاطر');
  String get myPermitsBlurb => _t(
        'View all active and historical site permits.',
        'عرض جميع تصاريح الموقع النشطة والسابقة.',
      );
  String get hazardReportsBlurb => _t(
        'View hazards reports and submit immediate safety findings or hazards.',
        'عرض بلاغات المخاطر وتقديم النتائج أو المخاطر الفورية للسلامة.',
      );
  String get openHazards => _t('OPEN HAZARDS', 'المخاطر المفتوحة');
  String get closedHazards => _t('CLOSED HAZARDS', 'المخاطر المغلقة');
  String get submitNewHazard => _t('Submit New Hazard', 'إرسال بلاغ جديد');
  String get noOpenHazards =>
      _t('No open hazards reported.', 'لا توجد مخاطر مفتوحة.');
  String get noClosedHazards =>
      _t('No closed hazards in history.', 'لا توجد مخاطر مغلقة في السجل.');
  String get hazardSeverityCritical => _t('Critical', 'حرج');
  String get hazardSeverityHigh => _t('High', 'شديد');
  String get hazardSeverityMedium => _t('Medium', 'متوسط');
  String get hazardSeverityLow => _t('Low', 'منخفض');
  String get hazardStatusSubmitted => _t('Submitted', 'مُقدَّم');
  String get hazardStatusTriaged => _t('Triaged', 'فُرز');
  String get hazardStatusInReview => _t('In Review', 'قيد المراجعة');
  String get hazardStatusAssigned => _t('Assigned', 'مُسند');
  String get hazardStatusInProgress => _t('In Progress', 'قيد المعالجة');
  String get hazardStatusResolved => _t('Resolved', 'تم الحل');
  String get hazardStatusDismissed => _t('Dismissed', 'مرفوض');
  String get hazardCategoryFall => _t('Fall', 'سقوط');
  String get hazardCategoryFire => _t('Fire', 'حريق');
  String get hazardCategoryElectrical => _t('Electrical', 'كهربائي');
  String get hazardCategoryToxic => _t('Toxic', 'مواد سامة');
  String get hazardCategoryImpact => _t('Impact', 'اصطدام');
  String get hazardCategoryOther => _t('Other', 'أخرى');
  String get hazardDescription => _t('Description', 'الوصف');
  String get hazardDescriptionHint => _t(
        'Describe what you saw, where, and why it is unsafe.',
        'صف ما رأيته وأين وسبب كونه غير آمن.',
      );
  String get hazardDescriptionRequired => _t(
        'Please describe the hazard (at least 5 characters).',
        'يرجى وصف الخطر (5 أحرف على الأقل).',
      );
  String get recentLogs => _t('RECENT LOGS', 'السجلات الأخيرة');
  String get hotWorkApproved =>
      _t('Hot Work Permit Approved', 'تمت الموافقة على تصريح العمل الساخن');
  String get gateEntryScan => _t('Gate Entry Scan', 'مسح بوابة الدخول');
  String get safetyContacts => _t('SAFETY CONTACTS', 'جهات اتصال السلامة');
  String get emergencyProtocol =>
      _t('EMERGENCY PROTOCOL', 'بروتوكول الطوارئ');
  String get emergencyTriggered =>
      _t('Emergency protocol triggered', 'تم تشغيل بروتوكول الطوارئ');
  String get noRecentActivity =>
      _t('No recent activity.', 'لا يوجد نشاط حديث.');
  String get loadingDashboard =>
      _t('Loading…', 'جارٍ التحميل…');
  String get couldNotLoadData =>
      _t('Could not load data.', 'تعذر تحميل البيانات.');
  String get scanGreen => _t('Green scan', 'مسح ناجح');
  String get scanRed => _t('Failed scan', 'مسح فاشل');
  String get scanImpersonation =>
      _t('Impersonation flagged', 'تم الإبلاغ عن انتحال هوية');
  String get gpsPermissionDenied => _t(
        'Location permission denied. GPS will not be attached.',
        'تم رفض إذن الموقع. لن يتم إرفاق GPS.',
      );
  String get gpsServicesOff =>
      _t('Location services are off.', 'خدمات الموقع مغلقة.');
  String orgRoleLabel(String role) => switch (role) {
        'client' => _t('Client', 'العميل'),
        'main_contractor' => _t('Main Contractor', 'المقاول الرئيسي'),
        'consultant' => _t('Consultant', 'الاستشاري'),
        'subcontractor' => _t('Subcontractor', 'مقاول من الباطن'),
        _ => role,
      };
  String userRoleLabel(String role) => switch (role) {
        'platform_admin' => _t('Platform Admin', 'مسؤول النظام'),
        'hse_manager' => _t('HSE Manager', 'مدير الصحة والسلامة'),
        'safety_engineer' => _t('Safety Engineer', 'مهندس سلامة'),
        'supervisor' => _t('Supervisor', 'مشرف'),
        'consultant' => _t('Consultant', 'استشاري'),
        'client_safety_lead' => _t('Client Safety Lead', 'مسؤول السلامة لدى العميل'),
        'auditor' => _t('Auditor', 'مدقق'),
        'worker' => _t('Worker', 'عامل'),
        _ => role,
      };

  // Tabs
  String get tabDashboard => _t('Dashboard', 'الرئيسية');
  String get tabScan => _t('Scan', 'المسح');
  String get tabPermits => _t('Permits', 'التصاريح');
  String get tabHazards => _t('Hazards', 'المخاطر');

  // Scan screen
  String get alignQr =>
      _t('ALIGN QR CODE WITHIN THE FRAME', 'قم بمحاذاة رمز الاستجابة داخل الإطار');
  String get enterIdManually =>
      _t('ENTER ID MANUALLY', 'إدخال المعرف يدوياً');
  String get manualEntry => _t('Manual entry', 'الإدخال اليدوي');
  String get manualEntryBlurb => _t(
        'Use the worker\'s employee ID if the QR code is damaged or unreadable.',
        'استخدم معرف الموظف إذا كان رمز الاستجابة تالفاً أو غير قابل للقراءة.',
      );
  String get verify => _t('VERIFY', 'تحقق');
  String get cameraUnavailable => _t('Camera unavailable', 'الكاميرا غير متاحة');
  String get useManualEntry =>
      _t('Use manual entry instead.', 'استخدم الإدخال اليدوي بدلاً من ذلك.');
  String get couldNotReachServer =>
      _t('Could not reach the server.', 'تعذر الوصول إلى الخادم.');
  String get couldNotProcessPhoto =>
      _t('Could not process photo', 'تعذرت معالجة الصورة');
  String get addPhotoOfHazard =>
      _t('Add a photo of the hazard.', 'أضف صورة للخطر.');
  String get couldNotSubmitNetwork => _t(
        'Could not submit. Check your network.',
        'تعذر الإرسال. تحقق من الشبكة.',
      );
  String get lhrAirportDevelopment =>
      _t('LHR Airport Development', 'تطوير مطار لندن هيثرو');

  // Scan result
  String get valid => _t('VALID', 'صالح');
  String get expired => _t('EXPIRED', 'منتهي الصلاحية');
  String get permitsTab => _t('PERMITS', 'التصاريح');
  String get certificationsTab => _t('CERTIFICATIONS', 'الشهادات');
  String get medicalTab => _t('MEDICAL', 'الطبية');
  String get scanAgain => _t('Scan Again', 'مسح مرة أخرى');
  String get contactSupport => _t('Contact Support', 'الاتصال بالدعم');
  String get expiresOn => _t('EXPIRES', 'تنتهي في');
  String get expiredOn => _t('EXPIRED', 'انتهت في');
  String get workerLabel => _t('WORKER', 'العامل');
  String get idNumberLabel => _t('ID NUMBER', 'رقم الهوية');
  String get employerLabel => _t('EMPLOYER', 'صاحب العمل');
  String get expiryInformation => _t('EXPIRY INFORMATION', 'معلومات الانتهاء');
  String get failureReason => _t('FAILURE REASON', 'سبب الفشل');
  String validUntil(String date) =>
      isAr ? 'صالح حتى ($date)' : 'Valid Until ($date)';
  String inductionExpired(String date) =>
      isAr ? 'انتهت الإحاطة التعريفية ($date)' : 'Induction Expired ($date)';
  String get verificationFailed => _t('Verification failed', 'فشل التحقق');
  String get certificationExpired => _t('Certification expired', 'انتهت صلاحية الشهادة');
  String get medicalFitnessExpired =>
      _t('Medical fitness expired', 'انتهت صلاحية اللياقة الطبية');
  String get impersonationFlagged =>
      _t('Impersonation flagged', 'تم الإبلاغ عن انتحال هوية');
  String get orgNotEngaged =>
      _t('Org not engaged for this site', 'المنظمة غير مرتبطة بهذا الموقع');
  String get supportPhone => _t('Support: +966 11 0000 0000', 'الدعم: +966 11 0000 0000');

  // Hazard report
  String get hazardCategory => _t('Hazard Category', 'فئة الخطر');
  String get hazFall => _t('Fall', 'سقوط');
  String get hazFire => _t('Fire', 'حريق');
  String get hazElectrical => _t('Electrical', 'كهربائي');
  String get hazToxic => _t('Toxic', 'سام');
  String get hazImpact => _t('Impact', 'اصطدام');
  String get hazOther => _t('Other', 'آخر');
  String get evidence => _t('Evidence', 'الأدلة');
  String get tapToAddPhoto => _t('Tap to add photo', 'اضغط لإضافة صورة');
  String get camera => _t('Camera', 'الكاميرا');
  String get gallery => _t('Gallery', 'المعرض');
  String get severity => _t('Severity', 'الخطورة');
  String get severityLow => _t('Low', 'منخفض');
  String get severityCritical => _t('Critical', 'حرج');
  String get severityCalloutDescription => _t(
        'Requires immediate supervisor intervention and area cordoning.',
        'يتطلب تدخلاً فورياً من المشرف وتطويق المنطقة.',
      );
  String severityLevelLabel(double v) {
    if (v < 0.25) return _t('Level 1: Low Risk', 'المستوى 1: خطر منخفض');
    if (v < 0.5) return _t('Level 2: Medium Risk', 'المستوى 2: خطر متوسط');
    if (v < 0.75) return _t('Level 3: Elevated Risk', 'المستوى 3: خطر مرتفع');
    if (v < 0.95) return _t('Level 4: High Risk', 'المستوى 4: خطر شديد');
    return _t('Level 5: Critical Risk', 'المستوى 5: خطر حرج');
  }
  String get location => _t('Location', 'الموقع');
  String get submitReport => _t('Submit Report', 'إرسال البلاغ');
  String get saveAsDraft => _t('Save as Draft', 'حفظ كمسودة');
  String get stepIdentity => _t('Identity', 'الهوية');
  String get stepDetails => _t('Details', 'التفاصيل');
  String get stepReview => _t('Review', 'مراجعة');
  String get attachGps => _t('Attach GPS coordinates', 'إرفاق إحداثيات GPS');
  String get privacyNotice => _t(
        'Your identity is never disclosed. Reports are encrypted in transit and at rest.',
        'لن يتم الكشف عن هويتك أبداً. البلاغات مشفرة أثناء النقل والتخزين.',
      );

  // Hazard submitted
  String get reportSubmittedTitle => _t(
        'Report Submitted\nSuccessfully',
        'تم إرسال البلاغ\nبنجاح',
      );
  String get trackingReference => _t('TRACKING REFERENCE', 'المرجع التتبعي');
  String reportIdLabel(String id) => isAr ? 'معرف البلاغ: #H-$id' : 'Report ID: #H-$id';
  String get status => _t('Status', 'الحالة');
  String get verified => _t('VERIFIED', 'تم التحقق');
  String get priority => _t('Priority', 'الأولوية');
  String get priorityHigh => _t('HIGH', 'مرتفعة');
  String get reference => _t('Reference', 'المرجع');
  String get returnToDashboard => _t('Return to Dashboard', 'العودة إلى الرئيسية');
  String get copyReportId => _t('Copy Report ID', 'نسخ معرف البلاغ');
  String get reportIdCopied => _t('Report ID copied', 'تم نسخ معرف البلاغ');
}

/// Riverpod provider so any ConsumerWidget can do
/// `final s = ref.watch(stringsProvider);` and stay reactive to the
/// language toggle.
final stringsProvider = Provider<AppStrings>(
  (ref) => AppStrings(ref.watch(appPreferencesProvider).locale),
);
