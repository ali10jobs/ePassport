import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../api/api_client.dart';
import '../../api/models.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

final _openHazardsProvider =
    FutureProvider.autoDispose<List<HazardReport>>((ref) async {
  final all = await ref.read(apiClientProvider).fetchHazardReports();
  return all.where((h) => !h.isClosed).toList();
});

final _closedHazardsProvider =
    FutureProvider.autoDispose<List<HazardReport>>((ref) async {
  final all = await ref.read(apiClientProvider).fetchHazardReports();
  return all.where((h) => h.isClosed).toList();
});

class HazardsScreen extends ConsumerWidget {
  const HazardsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final open = ref.watch(_openHazardsProvider);
    final closed = ref.watch(_closedHazardsProvider);

    return AppShell(
      tab: AppTab.hazards,
      child: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(_openHazardsProvider);
          ref.invalidate(_closedHazardsProvider);
          await Future.wait([
            ref.read(_openHazardsProvider.future),
            ref.read(_closedHazardsProvider.future),
          ]);
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
            const SizedBox(height: 16),
            _SubmitButton(
              label: s.submitNewHazard,
              onTap: () => context.push('/hazards/new'),
            ),
            const SizedBox(height: 24),
            _SectionLabel(s.openHazards),
            const SizedBox(height: 8),
            _HazardList(
              async: open,
              emptyMessage: s.noOpenHazards,
              s: s,
            ),
            const SizedBox(height: 24),
            _SectionLabel(s.closedHazards),
            const SizedBox(height: 8),
            _HazardList(
              async: closed,
              emptyMessage: s.noClosedHazards,
              s: s,
            ),
          ],
        ),
      ),
    );
  }
}

class _SubmitButton extends StatelessWidget {
  const _SubmitButton({required this.label, required this.onTap});
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: UiTokens.inkSolid,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
          child: Row(
            children: [
              Icon(Icons.add_circle_outline,
                  color: UiTokens.inkInverse, size: 22),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    color: UiTokens.inkInverse,
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.2,
                  ),
                ),
              ),
              Icon(Icons.arrow_forward,
                  color: UiTokens.inkInverse, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}

class _HazardList extends StatelessWidget {
  const _HazardList({
    required this.async,
    required this.emptyMessage,
    required this.s,
  });
  final AsyncValue<List<HazardReport>> async;
  final String emptyMessage;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    return async.when(
      data: (list) => list.isEmpty
          ? _EmptyState(message: emptyMessage)
          : Column(
              children: [
                for (final h in list) ...[
                  _HazardTile(report: h, s: s),
                  const SizedBox(height: 8),
                ],
              ],
            ),
      loading: () => _EmptyState(message: s.loadingDashboard),
      error: (_, __) => _EmptyState(message: s.couldNotLoadData),
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

  String _severityLabel() {
    switch (report.severity) {
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

  String _categoryLabel() {
    switch (report.category) {
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

  String _statusLabel() {
    switch (report.status) {
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

  @override
  Widget build(BuildContext context) {
    final ts = report.resolvedAt ?? report.createdAt;
    final dateStr = ts == null
        ? ''
        : DateFormat.yMMMd(s.isAr ? 'ar' : 'en').add_jm().format(ts.toLocal());
    final sevColor = _severityColor();
    return Container(
      decoration: BoxDecoration(
        color: UiTokens.surface,
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
                      _categoryLabel(),
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
                        _severityLabel(),
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
                  _statusLabel(),
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
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: TextStyle(
        color: UiTokens.muted,
        fontSize: 11,
        fontWeight: FontWeight.w700,
        letterSpacing: 1.2,
      ),
    );
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
