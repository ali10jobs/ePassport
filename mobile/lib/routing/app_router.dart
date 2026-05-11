import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/account/account_screen.dart';
import '../features/auth/login_screen.dart';
import '../features/dashboard/dashboard_screen.dart';
import '../features/hazard/anonymous_hazard_screen.dart';
import '../features/hazard/anonymous_hazard_submitted_screen.dart';
import '../features/hazard/hazards_screen.dart';
import '../features/launch/launch_screen.dart';
import '../features/permits/permits_screen.dart';
import '../features/profile/profile_screen.dart';
import '../features/scan/scan_screen.dart';
import '../features/settings/settings_screen.dart';
import '../features/splash/splash_screen.dart';
import 'auth_state.dart';

/// Router. Auth gate runs in `redirect`:
///   - while bootstrapping → /splash
///   - signed-in tabs: /dashboard, /scan, /permits, /hazards
///   - public pages: /launch, /login + the anonymous hazard tree
final routerProvider = Provider<GoRouter>((ref) => _buildRouter(ref));

GoRouter _buildRouter(Ref ref) {
  final notifier = _AuthRouteListener(ref);
  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: notifier,
    redirect: (context, state) {
      final auth = ref.read(authControllerProvider);
      final loc = state.matchedLocation;
      final isPublic = loc.startsWith('/launch') ||
          loc.startsWith('/login') ||
          loc.startsWith('/hazard/new') ||
          loc.startsWith('/hazard/submitted');
      if (auth.bootstrapping) {
        return loc == '/splash' ? null : '/splash';
      }
      if (!auth.isAuthenticated && !isPublic) return '/launch';
      if (auth.isAuthenticated &&
          (loc == '/launch' || loc == '/login' || loc == '/splash')) {
        return '/dashboard';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/launch', builder: (_, __) => const LaunchScreen()),
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/dashboard', builder: (_, __) => const DashboardScreen()),
      GoRoute(path: '/scan', builder: (_, __) => const ScanScreen()),
      GoRoute(path: '/permits', builder: (_, __) => const PermitsScreen()),
      GoRoute(path: '/profile', builder: (_, __) => const ProfileScreen()),
      GoRoute(path: '/account', builder: (_, __) => const AccountScreen()),
      GoRoute(path: '/settings', builder: (_, __) => const SettingsScreen()),
      GoRoute(
        path: '/hazards',
        builder: (_, __) => const HazardsScreen(),
      ),
      GoRoute(
        path: '/hazards/new',
        builder: (_, __) => const AnonymousHazardScreen(inAppShell: true),
      ),
      GoRoute(
        path: '/hazards/submitted/:id',
        builder: (_, state) => AnonymousHazardSubmittedScreen(
          anonymousReportId: state.pathParameters['id'] ?? '',
          inAppShell: true,
        ),
      ),
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
