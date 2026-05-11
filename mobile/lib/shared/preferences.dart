import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// App-wide user preferences: theme mode + locale.
/// Persisted in the OS keystore so they survive cold-start (alongside
/// the auth token, no extra dep). Also stores per-worker profile bio +
/// avatar override since the backend doesn't carry those yet.
class AppPreferences {
  final ThemeMode themeMode;
  final Locale locale;
  final Map<String, String> bios;
  final Map<String, String> avatarPaths;

  const AppPreferences({
    required this.themeMode,
    required this.locale,
    required this.bios,
    required this.avatarPaths,
  });

  static const fallback = AppPreferences(
    themeMode: ThemeMode.light,
    locale: Locale('en'),
    bios: {},
    avatarPaths: {},
  );

  AppPreferences copyWith({
    ThemeMode? themeMode,
    Locale? locale,
    Map<String, String>? bios,
    Map<String, String>? avatarPaths,
  }) {
    return AppPreferences(
      themeMode: themeMode ?? this.themeMode,
      locale: locale ?? this.locale,
      bios: bios ?? this.bios,
      avatarPaths: avatarPaths ?? this.avatarPaths,
    );
  }

  Map<String, dynamic> toJson() => {
        'themeMode': themeMode.name,
        'locale': locale.languageCode,
        'bios': bios,
        'avatarPaths': avatarPaths,
      };

  factory AppPreferences.fromJson(Map<String, dynamic> json) {
    return AppPreferences(
      themeMode: ThemeMode.values.firstWhere(
        (m) => m.name == json['themeMode'],
        orElse: () => ThemeMode.light,
      ),
      locale: Locale((json['locale'] as String?) ?? 'en'),
      bios: ((json['bios'] as Map?) ?? {}).map(
        (k, v) => MapEntry(k.toString(), v.toString()),
      ),
      avatarPaths: ((json['avatarPaths'] as Map?) ?? {}).map(
        (k, v) => MapEntry(k.toString(), v.toString()),
      ),
    );
  }
}

class AppPreferencesController extends StateNotifier<AppPreferences> {
  static const _key = 'epassport.preferences';
  final FlutterSecureStorage _storage;

  AppPreferencesController(this._storage) : super(AppPreferences.fallback) {
    _load();
  }

  Future<void> _load() async {
    final raw = await _storage.read(key: _key);
    if (raw == null) return;
    try {
      state = AppPreferences.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    } catch (_) {
      // ignore corrupt blob
    }
  }

  Future<void> _persist() async {
    await _storage.write(key: _key, value: jsonEncode(state.toJson()));
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    state = state.copyWith(themeMode: mode);
    await _persist();
  }

  Future<void> setLocale(Locale locale) async {
    state = state.copyWith(locale: locale);
    await _persist();
  }

  Future<void> setBio(String workerKey, String bio) async {
    final next = Map<String, String>.from(state.bios)..[workerKey] = bio;
    state = state.copyWith(bios: next);
    await _persist();
  }

  Future<void> setAvatarPath(String workerKey, String path) async {
    final next = Map<String, String>.from(state.avatarPaths)
      ..[workerKey] = path;
    state = state.copyWith(avatarPaths: next);
    await _persist();
  }
}

final _secureStorageProvider = Provider<FlutterSecureStorage>(
  (_) => const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  ),
);

final appPreferencesProvider =
    StateNotifierProvider<AppPreferencesController, AppPreferences>(
  (ref) => AppPreferencesController(ref.watch(_secureStorageProvider)),
);
