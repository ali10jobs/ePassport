import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:uuid/uuid.dart';

import 'api_exception.dart';
import 'auth_storage.dart';
import 'models.dart';

const String _kApiBase = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000',
);

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
              connectTimeout: const Duration(seconds: 8),
              receiveTimeout: const Duration(seconds: 12),
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
    ));
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

  // ---- Hazard reports (anonymous) ------------------------------------

  Future<AnonymousHazardSubmitted> submitAnonymousHazard({
    required String description,
    required String descriptionLang,
    required String severity,
    required String category,
    required List<int> photoBytes,
    String? projectId,
    double? latitude,
    double? longitude,
  }) async {
    final form = FormData.fromMap({
      'description': description,
      'description_lang': descriptionLang,
      'severity': severity,
      'category': category,
      if (projectId != null) 'project_id': projectId,
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
      'photo': MultipartFile.fromBytes(photoBytes, filename: 'hazard.jpg'),
    });

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
