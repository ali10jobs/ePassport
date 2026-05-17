import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:uuid/uuid.dart';

import 'api_exception.dart';
import 'auth_storage.dart';
import 'models.dart';

/// Default points at the hosted Render backend so release builds and
/// physical devices work out of the box. For local dev against a Laravel
/// server on your laptop, override at build time:
///
///   flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000   # Android emulator
///   flutter run --dart-define=API_BASE_URL=http://LAN_IP:8000     # physical device on Wi-Fi
const String _kApiBase = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'https://epassport-api-5s8v.onrender.com',
);

/// Public, top-level accessor so models can rewrite absolute asset URLs that
/// were minted by the backend against `127.0.0.1` / `localhost` (unreachable
/// from the Android emulator) to point at whatever API base this build uses.
String get apiBaseUrl => _kApiBase;

final authStorageProvider = Provider<AuthStorage>((ref) => AuthStorage());

final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(authStorageProvider);
  return ApiClient(authStorage: storage);
});

/// Dio-backed client. Sends a Sanctum bearer token, an Idempotency-Key on
/// every mutation, and unwraps the platform's stable error shape.
class ApiClient {
  final Dio _dio;
  final AuthStorage _authStorage;
  final Uuid _uuid = const Uuid();

  ApiClient({required AuthStorage authStorage, Dio? dio})
      : _authStorage = authStorage,
        _dio = dio ??
            Dio(BaseOptions(
              baseUrl: _kApiBase,
              // 60s covers Render's cold-start window (~30s) plus the
              // tail of a slow multipart upload over cellular.
              connectTimeout: const Duration(seconds: 60),
              receiveTimeout: const Duration(seconds: 90),
              sendTimeout: const Duration(seconds: 90),
              responseType: ResponseType.json,
              headers: {
                'Accept': 'application/json',
                HttpHeaders.contentTypeHeader: 'application/json',
              },
              validateStatus: (s) => s != null && s < 500,
            )) {
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _authStorage.readToken();
        if (token != null && token.isNotEmpty) {
          options.headers[HttpHeaders.authorizationHeader] = 'Bearer $token';
        }
        if (_isMutation(options.method) &&
            !options.headers.containsKey('Idempotency-Key')) {
          options.headers['Idempotency-Key'] = _uuid.v4();
        }
        handler.next(options);
      },
      // Retry transient upstream failures (Render cold start = 502/503/504,
      // network blips = connectionTimeout). Safe because every mutation
      // already carries an Idempotency-Key the server dedupes on; the key
      // is preserved across retries by the `containsKey` guard above.
      onError: (err, handler) async {
        if (_shouldRetry(err) &&
            (err.requestOptions.extra['retryCount'] as int? ?? 0) < 2) {
          final attempt =
              (err.requestOptions.extra['retryCount'] as int? ?? 0) + 1;
          err.requestOptions.extra['retryCount'] = attempt;
          // 2s, then 5s — long enough for a Render container to wake.
          await Future<void>.delayed(Duration(seconds: attempt * 2 + 1));
          try {
            final res = await _dio.fetch<dynamic>(err.requestOptions);
            return handler.resolve(res);
          } on DioException catch (e) {
            return handler.next(e);
          }
        }
        handler.next(err);
      },
    ));
  }

  /// Retry only on transient signals: gateway/proxy errors from upstream
  /// platforms (Render, Vercel, Cloudflare) and TCP-level timeouts.
  static bool _shouldRetry(DioException err) {
    if (err.type == DioExceptionType.connectionTimeout ||
        err.type == DioExceptionType.sendTimeout ||
        err.type == DioExceptionType.receiveTimeout ||
        err.type == DioExceptionType.connectionError) {
      return true;
    }
    final code = err.response?.statusCode;
    return code == 502 || code == 503 || code == 504;
  }

  static bool _isMutation(String method) {
    final m = method.toUpperCase();
    return m == 'POST' || m == 'PUT' || m == 'PATCH' || m == 'DELETE';
  }

  ApiException _toException(Response response) =>
      ApiException.fromBody(response.statusCode ?? 0, response.data);

  // ---- Auth ----------------------------------------------------------

  Future<LoginResponse> login({
    required String email,
    required String password,
    String deviceName = 'mobile',
  }) async {
    final res = await _dio.post('/api/v1/auth/login', data: {
      'email': email,
      'password': password,
      'mode': 'token',
      'device_name': deviceName,
    });
    if (res.statusCode != 200) throw _toException(res);
    return LoginResponse.fromJson(res.data as Map<String, dynamic>);
  }

  Future<MeUser?> me() async {
    final res = await _dio.get('/api/v1/me');
    if (res.statusCode == 401) return null;
    if (res.statusCode != 200) throw _toException(res);
    final data = (res.data as Map<String, dynamic>)['data'] as Map<String, dynamic>;
    return MeUser.fromJson(data);
  }

  Future<void> logout() async {
    final res = await _dio.post('/api/v1/auth/logout');
    if (res.statusCode != 200 && res.statusCode != 204 && res.statusCode != 401) {
      throw _toException(res);
    }
  }

  // ---- Scan ----------------------------------------------------------

  Future<ScanResult> verifyScan({
    String? token,
    String? employeeId,
    String? projectId,
    String? siteId,
  }) async {
    final res = await _dio.post('/api/v1/scans/verify', data: {
      if (token != null) 'token': token,
      if (employeeId != null) 'employee_id': employeeId,
      if (projectId != null) 'project_id': projectId,
      if (siteId != null) 'site_id': siteId,
      'client_app': Platform.isIOS ? 'mobile_ios' : 'mobile_android',
    });
    if (res.statusCode != 200) throw _toException(res);
    return ScanResult.fromJson(res.data as Map<String, dynamic>);
  }

  // ---- Dashboard / scans (live data for the mobile dashboard) -------

  /// Pulls the role-routed dashboard summary. The Laravel side exposes one
  /// endpoint per org-role; we pick from the caller's primary org so a main
  /// contractor sees their permits, a client sees aggregate stats, etc.
  Future<DashboardSummary> fetchDashboardSummary({required String orgRole}) async {
    final path = switch (orgRole) {
      'client' => '/api/v1/dashboards/client/summary',
      'main_contractor' => '/api/v1/dashboards/main-contractor/summary',
      'consultant' => '/api/v1/dashboards/consultant/summary',
      'subcontractor' => '/api/v1/dashboards/subcontractor/summary',
      _ => '/api/v1/dashboards/main-contractor/summary',
    };
    final res = await _dio.get(path);
    if (res.statusCode != 200) throw _toException(res);
    return DashboardSummary.fromJson(res.data as Map<String, dynamic>);
  }

  /// Recent scan events, newest first. We only ask for the small page the
  /// dashboard tile renders — full pagination lives on the web admin.
  Future<List<ScanLog>> fetchRecentScans({int limit = 5}) async {
    final res = await _dio.get('/api/v1/scans', queryParameters: {
      'per_page': limit,
      'sort': '-scanned_at',
    });
    if (res.statusCode != 200) throw _toException(res);
    final body = res.data as Map<String, dynamic>;
    final list = (body['data'] as List? ?? const []);
    return list
        .map((e) => ScanLog.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  /// Worker passport details — used after a green scan to surface the real
  /// name / trade / employer on the result screen.
  Future<WorkerSummary> fetchWorker(String workerId) async {
    final res = await _dio.get('/api/v1/workers/$workerId');
    if (res.statusCode != 200) throw _toException(res);
    return WorkerSummary.fromJson(res.data as Map<String, dynamic>);
  }

  /// Consolidated passport view — drives the Permits / Certifications /
  /// Medical tabs on the scan-result screen.
  Future<WorkerPassport> fetchWorkerPassport(String workerId) async {
    final res = await _dio.get('/api/v1/workers/$workerId/passport');
    if (res.statusCode != 200) throw _toException(res);
    return WorkerPassport.fromJson(res.data as Map<String, dynamic>);
  }

  /// Authenticated hazard reports list. `status` filter is forwarded to the
  /// backend QueryBuilder; pass null to skip filtering. Only reports the user
  /// is authorized to see are returned (org/project scoping is server-side).
  Future<List<HazardReport>> fetchHazardReports({
    String? status,
    int perPage = 25,
  }) async {
    final res = await _dio.get('/api/v1/hazard-reports', queryParameters: {
      'per_page': perPage,
      'sort': '-created_at',
      if (status != null) 'filter[status]': status,
    });
    if (res.statusCode != 200) throw _toException(res);
    final body = res.data as Map<String, dynamic>;
    final list = (body['data'] as List? ?? const []);
    return list
        .map((e) => HazardReport.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  Future<HazardReport> fetchHazardReport(String id) async {
    final res = await _dio.get('/api/v1/hazard-reports/$id');
    if (res.statusCode != 200) throw _toException(res);
    final body = res.data as Map<String, dynamic>;
    final data = (body['data'] as Map).cast<String, dynamic>();
    return HazardReport.fromJson(data);
  }

  // ---- Hazard reports (anonymous) ------------------------------------

  Future<AnonymousHazardSubmitted> submitAnonymousHazard({
    required String description,
    required String descriptionLang,
    required String severity,
    required String category,
    required List<List<int>> photos,
    String? projectId,
    double? latitude,
    double? longitude,
  }) async {
    assert(photos.isNotEmpty, 'at least one photo required');
    final form = FormData();
    form.fields.addAll([
      MapEntry('description', description),
      MapEntry('description_lang', descriptionLang),
      MapEntry('severity', severity),
      MapEntry('category', category),
      if (projectId != null) MapEntry('project_id', projectId),
      if (latitude != null) MapEntry('latitude', latitude.toString()),
      if (longitude != null) MapEntry('longitude', longitude.toString()),
    ]);
    // PHP/Laravel only parses repeated fields into an array when the key
    // carries the `[]` suffix; without it only the last value is kept and
    // validation reports "photos must be an array".
    for (var i = 0; i < photos.length; i++) {
      form.files.add(MapEntry(
        'photos[]',
        MultipartFile.fromBytes(photos[i], filename: 'hazard_$i.jpg'),
      ));
    }

    final res = await _dio.post(
      '/api/v1/hazard-reports/anonymous',
      data: form,
      options: Options(headers: {
        HttpHeaders.contentTypeHeader: 'multipart/form-data',
      }),
    );
    if (res.statusCode != 200 && res.statusCode != 201) throw _toException(res);
    return AnonymousHazardSubmitted.fromJson(
      res.data is String ? jsonDecode(res.data as String) as Map<String, dynamic>
                         : res.data as Map<String, dynamic>,
    );
  }
}
