/// Mobile mirror of the platform's stable error contract:
///   { "error": { "code": "...", "message": "...", "details": {...},
///                "request_id": "..." } }
class ApiException implements Exception {
  final int status;
  final String code;
  final String message;
  final Map<String, dynamic>? details;
  final String? requestId;

  ApiException({
    required this.status,
    required this.code,
    required this.message,
    this.details,
    this.requestId,
  });

  factory ApiException.fromBody(int status, dynamic body, {String? fallback}) {
    final err = (body is Map && body['error'] is Map) ? body['error'] as Map : null;
    return ApiException(
      status: status,
      code: err?['code']?.toString() ?? 'UNKNOWN_ERROR',
      message: err?['message']?.toString() ?? fallback ?? 'Request failed',
      details: (err?['details'] is Map)
          ? Map<String, dynamic>.from(err!['details'] as Map)
          : null,
      requestId: err?['request_id']?.toString(),
    );
  }

  @override
  String toString() => 'ApiException($status $code): $message';
}

/// Stable error codes the mobile UI cares about. Backend names are kept
/// verbatim so log lines line up across the stack.
class ErrorCodes {
  static const certExpired = 'CERT_EXPIRED';
  static const inductionMissing = 'INDUCTION_MISSING';
  static const medicalFail = 'MEDICAL_FAIL';
  static const orgNotEngaged = 'ORG_NOT_ENGAGED';
  static const impersonationFlag = 'IMPERSONATION_FLAG';
  static const equipmentTpiExpired = 'EQUIPMENT_TPI_EXPIRED';
  static const operatorNotAuthorized = 'OPERATOR_NOT_AUTHORIZED';
  static const unknownQr = 'UNKNOWN_QR';
  static const unauthenticated = 'UNAUTHENTICATED';
  static const validationFailed = 'VALIDATION_FAILED';
}
