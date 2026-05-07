import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Persists the Sanctum bearer token in the OS keystore (Keychain on iOS,
/// EncryptedSharedPreferences-backed Keystore on Android). The web app
/// uses localStorage; mobile gets the stronger guarantee.
class AuthStorage {
  static const _tokenKey = 'epassport.token';
  static const _userKey = 'epassport.user_json';

  final FlutterSecureStorage _storage;

  AuthStorage({FlutterSecureStorage? storage})
      : _storage = storage ??
            const FlutterSecureStorage(
              aOptions: AndroidOptions(encryptedSharedPreferences: true),
              iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
            );

  Future<String?> readToken() => _storage.read(key: _tokenKey);
  Future<void> writeToken(String token) => _storage.write(key: _tokenKey, value: token);

  Future<String?> readUserJson() => _storage.read(key: _userKey);
  Future<void> writeUserJson(String json) =>
      _storage.write(key: _userKey, value: json);

  Future<void> clear() async {
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _userKey);
  }
}
