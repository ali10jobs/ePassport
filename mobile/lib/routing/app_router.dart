import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/login_screen.dart';
import '../features/hazard/anonymous_hazard_screen.dart';
import '../features/hazard/anonymous_hazard_submitted_screen.dart';
import '../features/scan/scan_screen.dart';
import '../features/splash/splash_screen.dart';
import 'auth_state.dart';

/// Router. Auth gate runs in `redirect`:
///   - while bootstrapping → /splash
///   - signed-in pages: /scan
///   - public pages: /login + the anonymous hazard tree (workers must
///     be able to submit without an account)
final routerProvider = Provider<GoRouter>((ref) => _buildRouter(ref));

GoRouter _buildRouter(Ref ref) {
  final notifier = _AuthRouteListener(ref);
  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: notifier,
    redirect: (context, state) {
      final auth = ref.read(authControllerProvider);
      final loc = state.matchedLocation;
      final isPublic = loc.startsWith('/login') ||
          loc.startsWith('/hazard/new') ||
          loc.startsWith('/hazard/submitted');
      if (auth.bootstrapping) {
        return loc == '/splash' ? null : '/splash';
      }
      if (!auth.isAuthenticated && !isPublic) return '/login';
      if (auth.isAuthenticated && (loc == '/login' || loc == '/splash')) {
        return '/scan';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/scan', builder: (_, __) => const ScanScreen()),
      GoRoute(
        path: '/hazard/new',
        builder: (_, __) => const AnonymousHazardScreen(),
      ),
      GoRoute(
        path: '/hazard/submitted/:id',
        builder: (_, state) => AnonymousHazardSubmittedScreen(
          anonymousReportId: state.pathParameters['id'] ?? '',
        ),
      ),
    ],
    errorBuilder: (_, state) => Scaffold(
      body: Center(child: Text('Route not found: ${state.uri}')),
    ),
  );
}

class _AuthRouteListener extends ChangeNotifier {
  _AuthRouteListener(this._ref) {
    _ref.listen<AuthState>(authControllerProvider, (_, __) => notifyListeners());
  }
  final Ref _ref;
}
