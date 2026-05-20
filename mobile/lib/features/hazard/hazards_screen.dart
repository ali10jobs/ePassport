import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../api/api_client.dart';
import '../../api/models.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// Public so other screens (e.g. the post-submission confirmation) can call
/// `ref.invalidate(allHazardsProvider)` to force a fresh fetch when the user
/// returns here. Without that the autoDispose provider holds onto its cached
/// value because the HazardsScreen stays mounted underneath the pushed routes.
final allHazardsProvider =
    FutureProvider.autoDispose<List<HazardReport>>((ref) async {
  return ref.read(apiClientProvider).fetchHazardReports();
});

class HazardsScreen extends ConsumerStatefulWidget {
  const HazardsScreen({super.key});

  @override
  ConsumerState<HazardsScreen> createState() => _HazardsScreenState();
}

class _HazardsScreenState extends ConsumerState<HazardsScreen> {
  bool _showClosed = false;

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final async = ref.watch(allHazardsProvider);

    return AppShell(
      tab: AppTab.hazards,
      child: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(allHazardsProvider);
          await ref.read(allHazardsProvider.future);
        },
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
          children: [
            Text(
              s.hazardReports,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 11,
                fontWeight: FontWeight.w700,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              s.hazardReportsBlurb,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 16,
                fontWeight: FontWeight.w600,
                height: 1.35,
              ),
            ),
            const SizedBox(height: 18),
            async.when(
              data: (list) => _Stats(list: list, s: s),
              loading: () => const _StatsSkeleton(),
              error: (_, __) => const SizedBox.shrink(),
            ),
            const SizedBox(height: 16),
            _PrimaryPill(
              label: s.submitNewHazard,
              icon: Icons.arrow_forward,
              onTap: () => context.push('/hazards/new'),
            ),
            const SizedBox(height: 24),
            _ToggleRow(
              showClosed: _showClosed,
              onChanged: (v) => setState(() => _showClosed = v),
              s: s,
              async: async,
            ),
            const SizedBox(height: 12),
            async.when(
              data: (list) {
                final filtered =
                    list.where((h) => h.isClosed == _showClosed).toList();
                if (filtered.isEmpty) {
                  return _EmptyState(
                    message:
                        _showClosed ? s.noClosedHazards : s.noOpenHazards,
                  );
                }
                return Column(
                  children: [
                    for (final h in filtered) ...[
                      _HazardTile(report: h, s: s),
                      const SizedBox(height: 8),
                    ],
                  ],
                );
              },
              loading: () => _EmptyState(message: s.loadingDashboard),
              error: (_, __) => _EmptyState(message: s.couldNotLoadData),
            ),
          ],
        ),
      ),
    );
  }
}

class _Stats extends StatelessWidget {
  const _Stats({required this.list, required this.s});
  final List<HazardReport> list;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    final open = list.where((h) => !h.isClosed).toList();
    final closed = list.where((h) => h.isClosed).toList();
    final critical = open.where((h) => h.severity == 'critical').length;
    final high = open.where((h) => h.severity == 'high').length;
    final medium = open.where((h) => h.severity == 'medium').length;
    final low = open.where((h) => h.severity == 'low').length;
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _CountCard(
                label: s.openLabel,
                value: open.length.toString(),
                accent: UiTokens.destructive,
                icon: Icons.warning_amber_rounded,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _CountCard(
                label: s.closedLabel,
                value: closed.length.toString(),
                accent: UiTokens.success,
                icon: Icons.check_circle_outline,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _SeverityBreakdown(
          critical: critical,
          high: high,
          medium: medium,
          low: low,
          s: s,
        ),
      ],
    );
  }
}

class _CountCard extends StatelessWidget {
  const _CountCard({
    required this.label,
    required this.value,
    required this.accent,
    required this.icon,
  });
  final String label;
  final String value;
  final Color accent;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 14, 14, 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                label,
                style: TextStyle(
                  color: UiTokens.muted,
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.2,
                ),
              ),
              const Spacer(),
              Icon(icon, color: accent, size: 18),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 28,
              fontWeight: FontWeight.w800,
              height: 1,
            ),
          ),
        ],
      ),
    );
  }
}

class _SeverityBreakdown extends StatelessWidget {
  const _SeverityBreakdown({
    required this.critical,
    required this.high,
    required this.medium,
    required this.low,
    required this.s,
  });
  final int critical;
  final int high;
  final int medium;
  final int low;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            s.bySeverity,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: _SeverityChip(
                  label: s.hazardSeverityCritical,
                  count: critical,
                  color: UiTokens.danger,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _SeverityChip(
                  label: s.hazardSeverityHigh,
                  count: high,
                  color: UiTokens.destructive,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _SeverityChip(
                  label: s.hazardSeverityMedium,
                  count: medium,
                  color: UiTokens.warning,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _SeverityChip(
                  label: s.hazardSeverityLow,
                  count: low,
                  color: UiTokens.mutedStrong,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _SeverityChip extends StatelessWidget {
  const _SeverityChip({
    required this.label,
    required this.count,
    required this.color,
  });
  final String label;
  final int count;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.18)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            count.toString(),
            style: TextStyle(
              color: color,
              fontSize: 20,
              fontWeight: FontWeight.w800,
              height: 1,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 10,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.3,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}

class _StatsSkeleton extends StatelessWidget {
  const _StatsSkeleton();
  @override
  Widget build(BuildContext context) {
    return Container(
      height: 168,
      decoration: BoxDecoration(
        color: UiTokens.surfaceMuted,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: UiTokens.border),
      ),
    );
  }
}

class _ToggleRow extends StatelessWidget {
  const _ToggleRow({
    required this.showClosed,
    required this.onChanged,
    required this.s,
    required this.async,
  });
  final bool showClosed;
  final ValueChanged<bool> onChanged;
  final AppStrings s;
  final AsyncValue<List<HazardReport>> async;

  @override
  Widget build(BuildContext context) {
    final openCount = async.maybeWhen(
      data: (l) => l.where((h) => !h.isClosed).length,
      orElse: () => 0,
    );
    final closedCount = async.maybeWhen(
      data: (l) => l.where((h) => h.isClosed).length,
      orElse: () => 0,
    );
    return Row(
      children: [
        Expanded(
          child: Text(
            showClosed ? s.closedHazards : s.openHazards,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.2,
            ),
          ),
        ),
        _ToggleButton(
          label: showClosed ? s.showOpenHazards : s.showClosedHazards,
          badge: showClosed ? openCount : closedCount,
          onTap: () => onChanged(!showClosed),
        ),
      ],
    );
  }
}

class _ToggleButton extends StatelessWidget {
  const _ToggleButton({
    required this.label,
    required this.badge,
    required this.onTap,
  });
  final String label;
  final int badge;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: UiTokens.surface,
      shape: const StadiumBorder(),
      child: InkWell(
        customBorder: const StadiumBorder(),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: ShapeDecoration(
            shape: StadiumBorder(side: BorderSide(color: UiTokens.border)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: UiTokens.inkSolid,
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  badge.toString(),
                  style: TextStyle(
                    color: UiTokens.inkInverse,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PrimaryPill extends StatelessWidget {
  const _PrimaryPill({
    required this.label,
    required this.icon,
    required this.onTap,
  });
  final String label;
  final IconData icon;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 52,
      child: Material(
        color: UiTokens.ink,
        shape: const StadiumBorder(),
        child: InkWell(
          customBorder: const StadiumBorder(),
          onTap: onTap,
          child: Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    color: UiTokens.inkInverse,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(width: 8),
                Icon(icon, size: 18, color: UiTokens.inkInverse),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _HazardTile extends StatelessWidget {
  const _HazardTile({required this.report, required this.s});
  final HazardReport report;
  final AppStrings s;

  Color _severityColor() {
    switch (report.severity) {
      case 'critical':
        return UiTokens.danger;
      case 'high':
        return UiTokens.destructive;
      case 'medium':
        return UiTokens.warning;
      default:
        return UiTokens.muted;
    }
  }

  @override
  Widget build(BuildContext context) {
    final ts = report.resolvedAt ?? report.createdAt;
    final dateStr = ts == null
        ? ''
        : DateFormat.yMMMd(s.isAr ? 'ar' : 'en').add_jm().format(ts.toLocal());
    final sevColor = _severityColor();
    return Material(
      color: UiTokens.surface,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () => context.push('/hazards/${report.id}'),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: UiTokens.border),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            children: [
              Container(
                width: 8,
                height: 56,
                decoration: BoxDecoration(
                  color: sevColor,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          hazardCategoryLabel(s, report.category),
                          style: TextStyle(
                            color: UiTokens.ink,
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: sevColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            hazardSeverityLabel(s, report.severity),
                            style: TextStyle(
                              color: sevColor,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      hazardStatusLabel(s, report.status),
                      style: TextStyle(
                        color: UiTokens.mutedStrong,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (dateStr.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        dateStr,
                        style: TextStyle(
                          color: UiTokens.muted,
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              Icon(Icons.chevron_right, color: UiTokens.muted, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}

String hazardCategoryLabel(AppStrings s, String category) {
  switch (category) {
    case 'fall':
      return s.hazardCategoryFall;
    case 'fire':
      return s.hazardCategoryFire;
    case 'electrical':
      return s.hazardCategoryElectrical;
    case 'toxic':
      return s.hazardCategoryToxic;
    case 'impact':
      return s.hazardCategoryImpact;
    default:
      return s.hazardCategoryOther;
  }
}

String hazardSeverityLabel(AppStrings s, String severity) {
  switch (severity) {
    case 'critical':
      return s.hazardSeverityCritical;
    case 'high':
      return s.hazardSeverityHigh;
    case 'medium':
      return s.hazardSeverityMedium;
    default:
      return s.hazardSeverityLow;
  }
}

String hazardStatusLabel(AppStrings s, String status) {
  switch (status) {
    case 'triaged':
      return s.hazardStatusTriaged;
    case 'in_review':
      return s.hazardStatusInReview;
    case 'assigned':
      return s.hazardStatusAssigned;
    case 'in_progress':
      return s.hazardStatusInProgress;
    case 'resolved':
      return s.hazardStatusResolved;
    case 'dismissed':
      return s.hazardStatusDismissed;
    default:
      return s.hazardStatusSubmitted;
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
      decoration: BoxDecoration(
        color: UiTokens.surfaceMuted,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: UiTokens.border),
      ),
      child: Text(
        message,
        textAlign: TextAlign.center,
        style: TextStyle(color: UiTokens.muted, fontSize: 13),
      ),
    );
  }
}
