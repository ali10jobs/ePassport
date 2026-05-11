import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../api/models.dart';
import '../../routing/auth_state.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';
import 'dashboard_providers.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authControllerProvider).user;
    final s = ref.watch(stringsProvider);
    final isAr = s.isAr;
    final org = user?.organizations.isNotEmpty == true
        ? user!.organizations.firstWhere(
            (o) => o.isDefault,
            orElse: () => user.organizations.first,
          )
        : null;
    final orgName = org == null
        ? ''
        : (isAr ? org.nameAr : org.nameEn);
    final orgRole = org == null ? '' : s.userRoleLabel(org.role);
    final displayName = user?.name ?? '';

    final summary = ref.watch(dashboardSummaryProvider);
    final scans = ref.watch(recentScansProvider);

    return AppShell(
      tab: AppTab.dashboard,
      child: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(dashboardSummaryProvider);
          ref.invalidate(recentScansProvider);
          await Future.wait([
            ref.read(dashboardSummaryProvider.future),
            ref.read(recentScansProvider.future),
          ]);
        },
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
          children: [
            Text(
              s.engineerPortal,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 11,
                fontWeight: FontWeight.w700,
                letterSpacing: 1.2,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              s.welcomeBack(displayName),
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 22,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.3,
              ),
            ),
            const SizedBox(height: 20),
            _MetricCard(
              label: s.recentScans,
              value: summary.when(
                data: (d) => (d?.recentScans24h ?? 0).toString(),
                loading: () => '—',
                error: (_, __) => '—',
              ),
              valueSuffix: s.today,
              valueSuffixColor: UiTokens.muted,
              icon: Icons.qr_code_scanner,
            ),
            const SizedBox(height: 12),
            _MetricCard(
              label: s.pendingPermits,
              value: summary.when(
                data: (d) => (d?.pendingPermits ?? 0).toString(),
                loading: () => '—',
                error: (_, __) => '—',
              ),
              valueSuffix: s.actionRequired,
              valueSuffixColor: UiTokens.destructive,
              icon: Icons.assignment_late_outlined,
              iconColor: UiTokens.destructive,
            ),
            if (org != null) ...[
              const SizedBox(height: 12),
              _OrgCard(name: orgName, subtitle: orgRole, orgLabel: s.organization),
            ],
            const SizedBox(height: 20),
            _LinkRow(
              title: s.myPermits,
              subtitle: s.myPermitsBlurb,
              icon: Icons.assignment_outlined,
              onTap: () => context.go('/permits'),
            ),
            const SizedBox(height: 12),
            _LinkRow(
              title: s.hazardReports,
              subtitle: s.hazardReportsBlurb,
              icon: Icons.warning_amber_rounded,
              onTap: () => context.go('/hazards'),
            ),
            const SizedBox(height: 24),
            _SectionLabel(s.recentLogs),
            const SizedBox(height: 8),
            scans.when(
              data: (list) => list.isEmpty
                  ? _EmptyState(message: s.noRecentActivity)
                  : Column(
                      children: [
                        for (final entry in list) ...[
                          _ScanLogTile(scan: entry, s: s),
                          const SizedBox(height: 8),
                        ],
                      ],
                    ),
              loading: () => _EmptyState(message: s.loadingDashboard),
              error: (_, __) => _EmptyState(message: s.couldNotLoadData),
            ),
          ],
        ),
      ),
    );
  }
}

class _MetricCard extends StatelessWidget {
  _MetricCard({
    required this.label,
    required this.value,
    required this.valueSuffix,
    required this.valueSuffixColor,
    required this.icon,
    this.iconColor,
  });

  final String label;
  final String value;
  final String valueSuffix;
  final Color valueSuffixColor;
  final IconData icon;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(18, 16, 16, 18),
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
              Icon(icon, color: iconColor ?? UiTokens.ink, size: 22),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                value,
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 36,
                  fontWeight: FontWeight.w800,
                  height: 1,
                ),
              ),
              const SizedBox(width: 10),
              Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Text(
                  valueSuffix,
                  style: TextStyle(
                    color: valueSuffixColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.0,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _OrgCard extends StatelessWidget {
  const _OrgCard({
    required this.name,
    required this.subtitle,
    required this.orgLabel,
  });
  final String name;
  final String subtitle;
  final String orgLabel;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(18, 16, 16, 18),
      decoration: BoxDecoration(
        color: UiTokens.inkSolid,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                orgLabel,
                style: const TextStyle(
                  color: Color(0xFFA3A3A3),
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.2,
                ),
              ),
              const Spacer(),
              const Icon(Icons.business_outlined,
                  color: Color(0xFFA3A3A3), size: 20),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w800,
              height: 1.2,
            ),
          ),
          if (subtitle.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: const TextStyle(color: Color(0xFFA3A3A3), fontSize: 13),
            ),
          ],
        ],
      ),
    );
  }
}

class _LinkRow extends StatelessWidget {
  const _LinkRow({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
  });
  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: UiTokens.surface,
      borderRadius: BorderRadius.circular(8),
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.fromLTRB(14, 14, 14, 14),
          decoration: BoxDecoration(
            border: Border.all(color: UiTokens.border),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: UiTokens.surfaceMuted,
                  borderRadius: BorderRadius.circular(6),
                ),
                alignment: Alignment.center,
                child: Icon(icon, color: UiTokens.ink, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        color: UiTokens.ink,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.6,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: UiTokens.muted,
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.arrow_forward, color: UiTokens.ink, size: 18),
            ],
          ),
        ),
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

class _ScanLogTile extends StatelessWidget {
  const _ScanLogTile({required this.scan, required this.s});
  final ScanLog scan;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    final (icon, bg, fg, title) = switch (scan.result) {
      'green' => (Icons.check_circle_outline, UiTokens.successSoft,
          UiTokens.success, s.scanGreen),
      'impersonation_flag' => (Icons.report_problem_outlined,
          UiTokens.destructiveSoft, UiTokens.destructive, s.scanImpersonation),
      _ => (Icons.cancel_outlined, UiTokens.destructiveSoft,
          UiTokens.destructive, s.scanRed),
    };
    final subtitle = scan.scannedAt == null
        ? (scan.subjectId ?? '')
        : DateFormat.yMMMd(s.locale.toLanguageTag()).add_jm().format(scan.scannedAt!.toLocal());
    final trailing = scan.subjectId == null
        ? ''
        : 'EP-${scan.subjectId!.substring(0, scan.subjectId!.length.clamp(0, 6))}';
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: bg,
              borderRadius: BorderRadius.circular(6),
            ),
            alignment: Alignment.center,
            child: Icon(icon, color: fg, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 1),
                Text(
                  subtitle,
                  style: TextStyle(color: UiTokens.muted, fontSize: 12),
                ),
              ],
            ),
          ),
          if (trailing.isNotEmpty)
            Text(
              trailing,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
        ],
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
      padding: const EdgeInsets.symmetric(vertical: 24),
      alignment: Alignment.center,
      child: Text(
        message,
        style: TextStyle(color: UiTokens.muted, fontSize: 13),
      ),
    );
  }
}
