import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../api/api_client.dart';
import '../../api/models.dart';
import '../../routing/auth_state.dart';

/// Pulls the role-routed dashboard summary for the signed-in user's primary
/// organization. Returns null until the user is loaded.
final dashboardSummaryProvider =
    FutureProvider.autoDispose<DashboardSummary?>((ref) async {
  final auth = ref.watch(authControllerProvider);
  final user = auth.user;
  if (user == null || user.organizations.isEmpty) return null;
  final org = user.organizations.firstWhere(
    (o) => o.isDefault,
    orElse: () => user.organizations.first,
  );
  return ref.read(apiClientProvider).fetchDashboardSummary(orgRole: org.orgRole);
});

final recentScansProvider =
    FutureProvider.autoDispose<List<ScanLog>>((ref) async {
  final auth = ref.watch(authControllerProvider);
  if (auth.user == null) return const [];
  return ref.read(apiClientProvider).fetchRecentScans(limit: 5);
});
