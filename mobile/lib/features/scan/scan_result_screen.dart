import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../api/api_client.dart';
import '../../api/models.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// Scan result — matches:
///   - scan_result_navigation_refined (green / VALID)
///   - scan_result_failure_refined_annotated (red / EXPIRED)
///
/// Shows a green or red status banner, the worker portrait, tab strip
/// (Permits / Certifications / Medical), an expiry/failure card,
/// worker/id/employer metadata, and primary actions.
enum _ResultTab { permits, certifications, medical }

class ScanResultScreen extends ConsumerStatefulWidget {
  const ScanResultScreen({
    super.key,
    required this.result,
    required this.onDone,
  });

  final ScanResult result;
  final VoidCallback onDone;

  @override
  ConsumerState<ScanResultScreen> createState() => _ScanResultScreenState();
}

class _ScanResultScreenState extends ConsumerState<ScanResultScreen> {
  _ResultTab _tab = _ResultTab.permits;
  WorkerSummary? _worker;
  bool _loadingWorker = false;

  @override
  void initState() {
    super.initState();
    _maybeLoadWorker();
  }

  Future<void> _maybeLoadWorker() async {
    if (widget.result.subjectType != 'worker' || widget.result.subjectId == null) {
      return;
    }
    setState(() => _loadingWorker = true);
    try {
      final w = await ref.read(apiClientProvider).fetchWorker(widget.result.subjectId!);
      if (!mounted) return;
      setState(() {
        _worker = w;
        _loadingWorker = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingWorker = false);
    }
  }

  bool get _success => widget.result.result == ScanResultStatus.green;
  Color get _statusColor => _success ? UiTokens.success : UiTokens.destructive;
  IconData get _statusIcon =>
      _success ? Icons.check_circle_outline : Icons.cancel_outlined;

  String get _subjectId =>
      widget.result.subjectId ?? widget.result.eventId.substring(0, 8);

  String _displayName(AppStrings s) {
    if (widget.result.subjectType == 'equipment') return 'EQ-$_subjectId';
    final w = _worker;
    if (w != null) return s.isAr ? w.fullNameAr : w.fullNameEn;
    return _loadingWorker ? '…' : (widget.result.subjectId ?? '');
  }

  String _displayRole(AppStrings s) {
    if (widget.result.subjectType == 'equipment') return '';
    return _worker?.trade ?? '';
  }

  String _displayEmployer(AppStrings s) {
    final emp = _worker?.employer;
    if (emp == null) return '';
    return s.isAr ? emp.nameAr : emp.nameEn;
  }

  String _displayEmployeeId() => _worker?.employeeId ?? _subjectId;

  String _failureReason(AppStrings s) {
    if (widget.result.reasons.isEmpty) return s.verificationFailed;
    final r = widget.result.reasons.first;
    switch (r.code) {
      case 'CERT_EXPIRED':
        return s.certificationExpired;
      case 'INDUCTION_MISSING':
      case 'INDUCTION_EXPIRED':
        final d = _worker?.inductionValidUntil;
        return s.inductionExpired(
          d == null ? '' : DateFormat.yMd(s.locale.toLanguageTag()).format(d),
        );
      case 'MEDICAL_FAIL':
        return s.medicalFitnessExpired;
      case 'IMPERSONATION_FLAG':
        return s.impersonationFlagged;
      case 'ORG_NOT_ENGAGED':
        return s.orgNotEngaged;
      default:
        return r.code;
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final statusLabel = _success ? s.valid : s.expired;
    final expiryText = _worker?.inductionValidUntil == null
        ? '—'
        : s.validUntil(
            DateFormat.yMd(s.locale.toLanguageTag())
                .format(_worker!.inductionValidUntil!),
          );
    final displayName = _displayName(s);
    final role = _displayRole(s);
    final employer = _displayEmployer(s);
    return AppShell(
      tab: AppTab.scan,
      bodyPadding: const EdgeInsets.symmetric(horizontal: 0),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(0, 0, 0, 24),
        children: [
          _StatusBanner(color: _statusColor, icon: _statusIcon, label: statusLabel),
          const SizedBox(height: 20),
          _Portrait(borderColor: _success ? Colors.transparent : _statusColor),
          const SizedBox(height: 14),
          Center(
            child: Text(
              displayName,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 24,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          if (role.isNotEmpty) ...[
            const SizedBox(height: 2),
            Center(
              child: Text(
                role,
                style: TextStyle(color: UiTokens.muted, fontSize: 15),
              ),
            ),
          ],
          const SizedBox(height: 18),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _TabStrip(
              tab: _tab,
              onChanged: (t) => setState(() => _tab = t),
            ),
          ),
          const SizedBox(height: 14),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _success
                ? _ExpiryCard(label: s.expiryInformation, text: expiryText)
                : _FailureCard(label: s.failureReason, reason: _failureReason(s)),
          ),
          const SizedBox(height: 10),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: [
                Expanded(
                  child: _MetaCell(
                      label: s.workerLabel,
                      value: displayName.isEmpty ? '—' : displayName.split(' ').first),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _MetaCell(
                      label: s.idNumberLabel, value: _displayEmployeeId()),
                ),
              ],
            ),
          ),
          if (employer.isNotEmpty) ...[
            const SizedBox(height: 10),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: _MetaCell(label: s.employerLabel, value: employer),
            ),
          ],
          const SizedBox(height: 20),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _PillButton.primary(
              label: s.scanAgain,
              icon: Icons.qr_code_scanner,
              onTap: widget.onDone,
            ),
          ),
          const SizedBox(height: 10),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _PillButton.secondary(
              label: s.contactSupport,
              onTap: () => ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(s.supportPhone)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({
    required this.color,
    required this.icon,
    required this.label,
  });
  final Color color;
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: color,
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: Colors.white, size: 22),
          const SizedBox(width: 10),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.2,
            ),
          ),
        ],
      ),
    );
  }
}

class _Portrait extends StatelessWidget {
  const _Portrait({required this.borderColor});
  final Color borderColor;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        width: 140,
        height: 140,
        decoration: BoxDecoration(
          color: UiTokens.surfaceMuted,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: borderColor == Colors.transparent
                ? UiTokens.border
                : borderColor,
            width: borderColor == Colors.transparent ? 1 : 3,
          ),
        ),
        alignment: Alignment.center,
        child: Icon(Icons.person, size: 64, color: UiTokens.muted),
      ),
    );
  }
}

class _TabStrip extends ConsumerWidget {
  const _TabStrip({required this.tab, required this.onChanged});
  final _ResultTab tab;
  final ValueChanged<_ResultTab> onChanged;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Container(
      decoration: BoxDecoration(
        color: UiTokens.surfaceMuted,
        borderRadius: BorderRadius.circular(8),
      ),
      padding: const EdgeInsets.all(4),
      child: Row(
        children: [
          _TabPill(label: s.permitsTab, selected: tab == _ResultTab.permits, onTap: () => onChanged(_ResultTab.permits)),
          _TabPill(label: s.certificationsTab, selected: tab == _ResultTab.certifications, onTap: () => onChanged(_ResultTab.certifications)),
          _TabPill(label: s.medicalTab, selected: tab == _ResultTab.medical, onTap: () => onChanged(_ResultTab.medical)),
        ],
      ),
    );
  }
}

class _TabPill extends StatelessWidget {
  const _TabPill({
    required this.label,
    required this.selected,
    required this.onTap,
  });
  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 9),
          decoration: BoxDecoration(
            color: selected ? UiTokens.ink : Colors.transparent,
            borderRadius: BorderRadius.circular(6),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: TextStyle(
              color: selected ? Colors.white : UiTokens.ink,
              fontSize: 13,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ),
    );
  }
}

class _ExpiryCard extends StatelessWidget {
  const _ExpiryCard({required this.label, required this.text});
  final String label;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Container(
                width: 24,
                height: 24,
                decoration: BoxDecoration(
                  color: UiTokens.successSoft,
                  borderRadius: BorderRadius.circular(4),
                ),
                alignment: Alignment.center,
                child: Icon(Icons.event_available,
                    size: 16, color: UiTokens.success),
              ),
              const SizedBox(width: 10),
              Text(
                text,
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _FailureCard extends StatelessWidget {
  const _FailureCard({required this.label, required this.reason});
  final String label;
  final String reason;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Icon(Icons.history, color: UiTokens.destructive, size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  reason,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    height: 1.3,
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

class _MetaCell extends StatelessWidget {
  const _MetaCell({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class _PillButton extends StatelessWidget {
  const _PillButton.primary({
    required this.label,
    required this.onTap,
    this.icon,
  }) : _primary = true;

  const _PillButton.secondary({
    required this.label,
    required this.onTap,
  })  : _primary = false,
        icon = null;

  final String label;
  final VoidCallback onTap;
  final IconData? icon;
  final bool _primary;

  @override
  Widget build(BuildContext context) {
    final fg = _primary ? Colors.white : UiTokens.ink;
    final bg = _primary ? UiTokens.ink : UiTokens.surface;
    return SizedBox(
      height: 50,
      child: Material(
        color: bg,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(100),
          side: _primary
              ? BorderSide.none
              : BorderSide(color: UiTokens.border),
        ),
        child: InkWell(
          customBorder: const StadiumBorder(),
          onTap: onTap,
          child: Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 18, color: fg),
                  const SizedBox(width: 8),
                ],
                Text(
                  label,
                  style: TextStyle(
                    color: fg,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
