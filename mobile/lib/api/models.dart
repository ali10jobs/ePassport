/// Hand-written DTOs covering the demo-critical endpoints. Once the
/// OpenAPI snapshot pipeline lands, these get replaced by generated
/// classes — but for v1.0 hand-written keeps the surface minimal.
library;

class UserOrganization {
  final String id;
  final String nameEn;
  final String nameAr;
  final String role;
  final bool isDefault;

  UserOrganization({
    required this.id,
    required this.nameEn,
    required this.nameAr,
    required this.role,
    required this.isDefault,
  });

  factory UserOrganization.fromJson(Map<String, dynamic> json) => UserOrganization(
        id: json['id'] as String,
        nameEn: json['name_en'] as String,
        nameAr: json['name_ar'] as String,
        role: json['role'] as String,
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
