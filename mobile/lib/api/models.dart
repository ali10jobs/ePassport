/// Hand-written DTOs covering the demo-critical endpoints. Once the
/// OpenAPI snapshot pipeline lands, these get replaced by generated
/// classes — but for v1.0 hand-written keeps the surface minimal.
library;

class UserOrganization {
  final String id;
  final String nameEn;
  final String nameAr;
  /// User's role within the organization (e.g. hse_manager).
  final String role;
  /// Organization's role in projects (client / main_contractor / consultant /
  /// subcontractor). Drives which dashboard endpoint we hit.
  final String orgRole;
  final bool isDefault;

  UserOrganization({
    required this.id,
    required this.nameEn,
    required this.nameAr,
    required this.role,
    required this.orgRole,
    required this.isDefault,
  });

  factory UserOrganization.fromJson(Map<String, dynamic> json) => UserOrganization(
        id: json['id'] as String,
        nameEn: json['name_en'] as String,
        nameAr: json['name_ar'] as String,
        role: json['role'] as String,
        orgRole: json['org_role'] as String? ?? 'main_contractor',
        isDefault: json['is_default'] as bool? ?? false,
      );
}

class MeUser {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String locale;
  final List<UserOrganization> organizations;

  MeUser({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.locale,
    required this.organizations,
  });

  factory MeUser.fromJson(Map<String, dynamic> json) => MeUser(
        id: (json['id'] as num).toInt(),
        name: json['name'] as String,
        email: json['email'] as String,
        phone: json['phone'] as String?,
        locale: json['locale'] as String? ?? 'en',
        organizations: (json['organizations'] as List? ?? [])
            .map((e) => UserOrganization.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class LoginResponse {
  final String? accessToken;
  final String tokenType;
  final MeUser user;

  LoginResponse({required this.accessToken, required this.tokenType, required this.user});

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>;
    return LoginResponse(
      accessToken: data['access_token'] as String?,
      tokenType: data['token_type'] as String? ?? 'Bearer',
      user: MeUser.fromJson(data['user'] as Map<String, dynamic>),
    );
  }
}

enum ScanResultStatus { green, red, impersonationFlag }

ScanResultStatus parseScanResultStatus(String raw) {
  switch (raw) {
    case 'green':
      return ScanResultStatus.green;
    case 'red':
      return ScanResultStatus.red;
    case 'impersonation_flag':
      return ScanResultStatus.impersonationFlag;
    default:
      return ScanResultStatus.red;
  }
}

class ScanReason {
  final String code;
  final Map<String, dynamic>? details;

  ScanReason({required this.code, this.details});

  factory ScanReason.fromJson(Map<String, dynamic> json) => ScanReason(
        code: json['code'] as String,
        details: (json['details'] is Map)
            ? Map<String, dynamic>.from(json['details'] as Map)
            : null,
      );
}

class ScanResult {
  final ScanResultStatus result;
  final String? subjectType;
  final String? subjectId;
  final String? tokenType;
  final List<ScanReason> reasons;
  final String eventId;
  final DateTime scannedAt;

  ScanResult({
    required this.result,
    required this.subjectType,
    required this.subjectId,
    required this.tokenType,
    required this.reasons,
    required this.eventId,
    required this.scannedAt,
  });

  factory ScanResult.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] is Map ? json['data'] : json) as Map<String, dynamic>;
    return ScanResult(
      result: parseScanResultStatus(data['result'] as String),
      subjectType: data['subject_type'] as String?,
      subjectId: data['subject_id'] as String?,
      tokenType: data['token_type'] as String?,
      reasons: (data['reasons'] as List? ?? [])
          .map((e) => ScanReason.fromJson(e as Map<String, dynamic>))
          .toList(),
      eventId: data['event_id'] as String,
      scannedAt: DateTime.parse(data['scanned_at'] as String),
    );
  }
}

/// Role-routed dashboard payload. Mirrors the per-role keys returned by
/// `/api/v1/dashboards/{role}/summary`. We only surface counts the mobile
/// dashboard needs — recent scans, pending permits, project context.
class DashboardSummary {
  final int recentScans24h;
  final int pendingPermits;
  final int openCriticalHazards;

  DashboardSummary({
    required this.recentScans24h,
    required this.pendingPermits,
    required this.openCriticalHazards,
  });

  factory DashboardSummary.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] is Map ? json['data'] : json) as Map<String, dynamic>;
    final scans = (data['scans'] as Map?)?.cast<String, dynamic>() ?? const {};
    final permits = (data['permits'] as Map?)?.cast<String, dynamic>() ?? const {};
    final hazards = (data['hazards'] as Map?)?.cast<String, dynamic>() ?? const {};
    final incidents = (data['incident_indicators'] as Map?)?.cast<String, dynamic>() ?? const {};
    return DashboardSummary(
      recentScans24h: _asInt(scans['total_24h']),
      pendingPermits: _asInt(permits['awaiting_review']) + _asInt(permits['drafts']),
      openCriticalHazards: _asInt(hazards['open_critical']) +
          _asInt(incidents['critical_hazards_open']),
    );
  }

  static int _asInt(Object? v) => v is num ? v.toInt() : 0;
}

class ScanLog {
  final String id;
  final String? subjectType;
  final String? subjectId;
  final String result;
  final DateTime? scannedAt;

  ScanLog({
    required this.id,
    required this.subjectType,
    required this.subjectId,
    required this.result,
    required this.scannedAt,
  });

  factory ScanLog.fromJson(Map<String, dynamic> json) => ScanLog(
        id: json['id'].toString(),
        subjectType: json['subject_type'] as String?,
        subjectId: json['subject_id'] as String?,
        result: json['result'] as String? ?? 'red',
        scannedAt: json['scanned_at'] != null
            ? DateTime.tryParse(json['scanned_at'] as String)
            : null,
      );
}

class WorkerEmployer {
  final String id;
  final String nameEn;
  final String nameAr;
  WorkerEmployer({required this.id, required this.nameEn, required this.nameAr});
  factory WorkerEmployer.fromJson(Map<String, dynamic> json) => WorkerEmployer(
        id: json['id'] as String,
        nameEn: json['name_en'] as String? ?? '',
        nameAr: json['name_ar'] as String? ?? '',
      );
}

class WorkerSummary {
  final String id;
  final String fullNameEn;
  final String fullNameAr;
  final String? trade;
  final String? employeeId;
  final WorkerEmployer? employer;
  final DateTime? inductionValidUntil;

  WorkerSummary({
    required this.id,
    required this.fullNameEn,
    required this.fullNameAr,
    required this.trade,
    required this.employeeId,
    required this.employer,
    required this.inductionValidUntil,
  });

  factory WorkerSummary.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] is Map ? json['data'] : json) as Map<String, dynamic>;
    return WorkerSummary(
      id: data['id'].toString(),
      fullNameEn: (data['full_name_en'] as String?) ??
          '${data['first_name_en'] ?? ''} ${data['last_name_en'] ?? ''}'.trim(),
      fullNameAr: (data['full_name_ar'] as String?) ??
          '${data['first_name_ar'] ?? ''} ${data['last_name_ar'] ?? ''}'.trim(),
      trade: data['trade'] as String?,
      employeeId: data['employee_id'] as String?,
      employer: data['employer_organization'] is Map
          ? WorkerEmployer.fromJson(
              (data['employer_organization'] as Map).cast<String, dynamic>())
          : null,
      inductionValidUntil: data['induction_valid_until'] != null
          ? DateTime.tryParse(data['induction_valid_until'] as String)
          : null,
    );
  }
}

/// Hazard report list item — what the mobile hazards inbox renders.
class HazardReport {
  final String id;
  final String? anonymousReportId;
  final bool isAnonymous;
  final String category;
  final String severity;
  final String status;
  final String? description;
  final String? descriptionLang;
  final String? photoPath;
  final List<String> photoPaths;
  final List<HazardPhotoLink> photos;
  final double? latitude;
  final double? longitude;
  final DateTime? createdAt;
  final DateTime? resolvedAt;

  HazardReport({
    required this.id,
    required this.anonymousReportId,
    required this.isAnonymous,
    required this.category,
    required this.severity,
    required this.status,
    required this.description,
    required this.descriptionLang,
    required this.photoPath,
    required this.photoPaths,
    required this.photos,
    required this.latitude,
    required this.longitude,
    required this.createdAt,
    required this.resolvedAt,
  });

  bool get isClosed => status == 'resolved' || status == 'dismissed';

  factory HazardReport.fromJson(Map<String, dynamic> json) => HazardReport(
        id: json['id'].toString(),
        anonymousReportId: json['anonymous_report_id'] as String?,
        isAnonymous: json['is_anonymous'] as bool? ?? false,
        category: json['category'] as String? ?? 'other',
        severity: json['severity'] as String? ?? 'medium',
        status: json['status'] as String? ?? 'submitted',
        description: json['description'] as String?,
        descriptionLang: json['description_lang'] as String?,
        photoPath: json['photo_path'] as String?,
        photoPaths: (json['photo_paths'] as List? ?? const [])
            .map((e) => e.toString())
            .toList(),
        photos: (json['photos'] as List? ?? const [])
            .whereType<Map>()
            .map((e) => HazardPhotoLink.fromJson(e.cast<String, dynamic>()))
            .toList(),
        latitude: _asDouble(json['latitude']),
        longitude: _asDouble(json['longitude']),
        createdAt: json['created_at'] != null
            ? DateTime.tryParse(json['created_at'] as String)
            : null,
        resolvedAt: json['resolved_at'] != null
            ? DateTime.tryParse(json['resolved_at'] as String)
            : null,
      );
}

class HazardPhotoLink {
  final int index;
  final String url;
  HazardPhotoLink({required this.index, required this.url});
  factory HazardPhotoLink.fromJson(Map<String, dynamic> json) => HazardPhotoLink(
        index: (json['index'] as num?)?.toInt() ?? 0,
        url: json['url'] as String? ?? '',
      );
}

double? _asDouble(Object? v) {
  if (v == null) return null;
  if (v is num) return v.toDouble();
  if (v is String) return double.tryParse(v);
  return null;
}

/// Server response after submitting an anonymous hazard. Mobile shows the
/// id with a copy button so the reporter can check status later via the
/// public web page (/hazard-status?id=...).
class AnonymousHazardSubmitted {
  final String anonymousReportId;
  final String status;
  final DateTime submittedAt;

  AnonymousHazardSubmitted({
    required this.anonymousReportId,
    required this.status,
    required this.submittedAt,
  });

  factory AnonymousHazardSubmitted.fromJson(Map<String, dynamic> json) {
    final data = (json['data'] is Map ? json['data'] : json) as Map<String, dynamic>;
    return AnonymousHazardSubmitted(
      anonymousReportId: data['anonymous_report_id'] as String,
      status: data['status'] as String? ?? 'submitted',
      submittedAt: DateTime.parse(data['submitted_at'] as String),
    );
  }
}
