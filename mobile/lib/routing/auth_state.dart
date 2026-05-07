import 'dart:async';
import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../api/api_client.dart';
import '../api/auth_storage.dart';
import '../api/models.dart';

/// Auth state surfaces three things to the router:
///   - bootstrapping (we haven't checked the token yet → splash)
///   - user (signed in)
///   - null (signed out)
class AuthState {
  final bool bootstrapping;
  final MeUser? user;

  const AuthState({required this.bootstrapping, required this.user});

  bool get isAuthenticated => !bootstrapping && user != null;

  AuthState copy({bool? bootstrapping, MeUser? user, bool clearUser = false}) {
    return AuthState(
      bootstrapping: bootstrapping ?? this.bootstrapping,
      user: clearUser ? null : (user ?? this.user),
    );
  }
}

class AuthController extends StateNotifier<AuthState> {
  final ApiClient _api;
  final AuthStorage _storage;

  AuthController(this._api, this._storage)
      : super(const AuthState(bootstrapping: true, user: null)) {
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    try {
      final token = await _storage.readToken();
      if (token == null) {
        state = const AuthState(bootstrapping: false, user: null);
        return;
      }
      final cachedJson = await _storage.readUserJson();
      MeUser? cached;
      if (cachedJson != null) {
        cached = MeUser.fromJson(jsonDecode(cachedJson) as Map<String, dynamic>);
      }
      // Optimistically render cached user, then refresh in the background.
      state = AuthState(bootstrapping: false, user: cached);
      try {
        final fresh = await _api.me();
        state = AuthState(bootstrapping: false, user: fresh);
        if (fresh == null) {
          await _storage.clear();
        }
      } catch (_) {
        // Network error — keep cached user; the next API call will surface it.
      }
    } catch (_) {
      state = const AuthState(bootstrapping: false, user: null);
    }
  }

  Future<void> signIn({required String email, required String password}) async {
    final res = await _api.login(email: email, password: password);
    if (res.accessToken != null) {
      await _storage.writeToken(res.accessToken!);
    }
    await _storage.writeUserJson(jsonEncode({
      'id': res.user.id,
      'name': res.user.name,
      'email': res.user.email,
      'phone': res.user.phone,
      'locale': res.user.locale,
      'organizations': res.user.organizations
          .map((o) => {
                'id': o.id,
                'name_en': o.nameEn,
                'name_ar': o.nameAr,
                'role': o.role,
                'is_default': o.isDefault,
              })
          .toList(),
    }));
    state = AuthState(bootstrapping: false, user: res.user);
  }

  Future<void> signOut() async {
    try {
      await _api.logout();
    } catch (_) {
      // Token may already be invalid server-side — nothing to do.
    }
    await _storage.clear();
    state = const AuthState(bootstrapping: false, user: null);
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(
    ref.watch(apiClientProvider),
    ref.watch(authStorageProvider),
  );
});
