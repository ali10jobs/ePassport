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
  WorkerPassport? _passport;
  bool _loadingWorker = false;

  WorkerSummary? get _worker => _passport?.summary;

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
      final p = await ref
          .read(apiClientProvider)
          .fetchWorkerPassport(widget.result.subjectId!);
      if (!mounted) return;
      setState(() {
        _passport = p;
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

  /// True when the manually-entered id (or scanned token) didn't resolve to
  /// any worker/equipment. Backend returns reason code UNKNOWN_QR with
  /// subject_id == null.
  bool get _notFound {
    if (widget.result.subjectId != null) return false;
    for (final r in widget.result.reasons) {
      if (r.code == 'UNKNOWN_QR') return true;
    }
    return false;
  }

  /// True when the worker has never been inducted (vs. inducted-but-expired).
  /// Server returns reason INDUCTION_MISSING for both cases; we differentiate
  /// using details.status === 'not_inducted'.
  bool get _noPermit {
    for (final r in widget.result.reasons) {
      if (r.code == 'INDUCTION_MISSING' || r.code == 'INDUCTION_EXPIRED') {
        if (r.details?['status'] == 'not_inducted') return true;
      }
    }
    return false;
  }

  String _failureReason(AppStrings s) {
    if (widget.result.reasons.isEmpty) return s.verificationFailed;
    final r = widget.result.reasons.first;
    switch (r.code) {
      case 'CERT_EXPIRED':
        return s.certificationExpired;
      case 'INDUCTION_MISSING':
      case 'INDUCTION_EXPIRED':
        if (_noPermit) return s.noPermitReason;
        final d = _worker?.inductionValidUntil;
        return d == null
            ? s.noPermitReason
            : s.permitExpiredOn(
                DateFormat.yMd(s.locale.toLanguageTag()).format(d),
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
    if (_notFound) {
      return _NotFoundScreen(onDone: widget.onDone);
    }
    final statusLabel = _success ? s.valid : s.accessDenied;
    final expiryText = _worker?.inductionValidUntil == null
        ? (_noPermit ? s.noPermitOnFile : '—')
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
          _Portrait(
            borderColor: _success ? Colors.transparent : _statusColor,
            photoUrl: _worker?.photoUrl,
          ),
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
            child: _TabContent(
              tab: _tab,
              success: _success,
              passport: _passport,
              expiryText: expiryText,
              failureLabel: s.failureReason,
              failureReason: _failureReason(s),
              expiryLabel: s.expiryInformation,
            ),
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
  const _Portrait({required this.borderColor, this.photoUrl});
  final Color borderColor;
  final String? photoUrl;

  @override
  Widget build(BuildContext context) {
    final url = photoUrl;
    final hasPhoto = url != null && url.isNotEmpty;
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
        clipBehavior: Clip.antiAlias,
        alignment: Alignment.center,
        child: hasPhoto
            ? Image.network(
                url,
                fit: BoxFit.cover,
                width: 140,
                height: 140,
                loadingBuilder: (context, child, progress) {
                  if (progress == null) return child;
                  return Center(
                    child: SizedBox.square(
                      dimension: 28,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        value: progress.expectedTotalBytes == null
                            ? null
                            : progress.cumulativeBytesLoaded /
                                progress.expectedTotalBytes!,
                      ),
                    ),
                  );
                },
                errorBuilder: (_, _, _) =>
                    Icon(Icons.person, size: 64, color: UiTokens.muted),
              )
            : Icon(Icons.person, size: 64, color: UiTokens.muted),
      ),
    );
  }
}

/// Segmented tab strip whose selected-pill width hugs the label text and
/// slides between positions when the active tab changes. The strip itself
/// is intrinsic-width and centered in its parent.
class _TabStrip extends ConsumerWidget {
  const _TabStrip({required this.tab, required this.onChanged});
  final _ResultTab tab;
  final ValueChanged<_ResultTab> onChanged;

  // Layout constants kept in one place so the TextPainter measurements and
  // the rendered cells stay in sync.
  static const double _padX = 18;
  static const double _padY = 9;
  static const double _trackPad = 4;
  static const TextStyle _labelStyle = TextStyle(
    fontSize: 13,
    fontWeight: FontWeight.w700,
  );

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final tabs = <(String, _ResultTab)>[
      (s.permitsTab, _ResultTab.permits),
      (s.certificationsTab, _ResultTab.certifications),
      (s.medicalTab, _ResultTab.medical),
    ];
    final dir = Directionality.of(context);
    final scaler = MediaQuery.textScalerOf(context);

    // Pre-measure each label so the selected pill can size to text + padding.
    final widths = tabs.map((t) {
      final tp = TextPainter(
        text: TextSpan(text: t.$1, style: _labelStyle),
        textDirection: dir,
        textScaler: scaler,
      )..layout();
      return tp.width + _padX * 2;
    }).toList();

    // Cumulative offsets from the start edge of the inner row.
    final offsets = <double>[];
    double cursor = 0;
    for (final w in widths) {
      offsets.add(cursor);
      cursor += w;
    }
    final activeIdx = tabs.indexWhere((t) => t.$2 == tab);
    final pillLeft = offsets[activeIdx] + _trackPad;
    final pillWidth = widths[activeIdx];
    final cellHeight =
        (_labelStyle.fontSize ?? 13) * scaler.scale(1) + _padY * 2;

    return Center(
      child: Container(
        decoration: BoxDecoration(
          color: UiTokens.surfaceMuted,
          borderRadius: BorderRadius.circular(999),
        ),
        padding: const EdgeInsets.all(_trackPad),
        child: SizedBox(
          width: cursor,
          height: cellHeight,
          child: Stack(
            children: [
              AnimatedPositionedDirectional(
                duration: const Duration(milliseconds: 240),
                curve: Curves.easeOutCubic,
                start: pillLeft - _trackPad,
                top: 0,
                bottom: 0,
                width: pillWidth,
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
              ),
              Row(
                children: [
                  for (var i = 0; i < tabs.length; i++)
                    SizedBox(
                      width: widths[i],
                      height: cellHeight,
                      child: GestureDetector(
                        behavior: HitTestBehavior.opaque,
                        onTap: () => onChanged(tabs[i].$2),
                        child: Center(
                          child: AnimatedDefaultTextStyle(
                            duration: const Duration(milliseconds: 240),
                            curve: Curves.easeOutCubic,
                            style: _labelStyle.copyWith(
                              color: i == activeIdx
                                  ? Colors.black
                                  : UiTokens.muted,
                            ),
                            child: Text(tabs[i].$1),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
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
    final fg = _primary ? Colors.black : UiTokens.ink;
    final bg = _primary ? Colors.white : UiTokens.surface;
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

class _NotFoundScreen extends ConsumerWidget {
  const _NotFoundScreen({required this.onDone});
  final VoidCallback onDone;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return AppShell(
      tab: AppTab.scan,
      bodyPadding: const EdgeInsets.symmetric(horizontal: 0),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(0, 0, 0, 24),
        children: [
          _StatusBanner(
            color: UiTokens.destructive,
            icon: Icons.search_off,
            label: s.notFoundBanner,
          ),
          const SizedBox(height: 28),
          Center(
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                color: UiTokens.surfaceMuted,
                shape: BoxShape.circle,
                border: Border.all(color: UiTokens.destructive, width: 3),
              ),
              alignment: Alignment.center,
              child: Icon(Icons.person_off_outlined,
                  size: 56, color: UiTokens.destructive),
            ),
          ),
          const SizedBox(height: 22),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Text(
              s.employeeNotFoundTitle,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 22,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: Text(
              s.employeeNotFoundBlurb,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 14,
                height: 1.5,
              ),
            ),
          ),
          const SizedBox(height: 28),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: _PillButton.primary(
              label: s.tryAgain,
              icon: Icons.qr_code_scanner,
              onTap: onDone,
            ),
          ),
        ],
      ),
    );
  }
}

class _TabContent extends ConsumerWidget {
  const _TabContent({
    required this.tab,
    required this.success,
    required this.passport,
    required this.expiryText,
    required this.failureLabel,
    required this.failureReason,
    required this.expiryLabel,
  });
  final _ResultTab tab;
  final bool success;
  final WorkerPassport? passport;
  final String expiryText;
  final String failureLabel;
  final String failureReason;
  final String expiryLabel;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    switch (tab) {
      case _ResultTab.permits:
        return success
            ? _ExpiryCard(label: expiryLabel, text: expiryText)
            : _FailureCard(label: failureLabel, reason: failureReason);
      case _ResultTab.certifications:
        return _CertificationsList(
          certifications: passport?.certifications ?? const [],
          isAr: s.isAr,
          s: s,
        );
      case _ResultTab.medical:
        return _MedicalProfileCard(passport: passport, s: s);
    }
  }
}

class _CertificationsList extends StatelessWidget {
  const _CertificationsList({
    required this.certifications,
    required this.isAr,
    required this.s,
  });
  final List<WorkerCertification> certifications;
  final bool isAr;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    if (certifications.isEmpty) {
      return _EmptyCard(label: s.noCertifications);
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < certifications.length; i++) ...[
          if (i > 0) const SizedBox(height: 10),
          _CertCard(cert: certifications[i], isAr: isAr, s: s),
        ],
      ],
    );
  }
}

class _CertCard extends StatelessWidget {
  const _CertCard({required this.cert, required this.isAr, required this.s});
  final WorkerCertification cert;
  final bool isAr;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    final expired = cert.isExpired;
    final df = DateFormat.yMd(s.locale.toLanguageTag());
    final name = (isAr ? cert.typeNameAr : cert.typeNameEn) ??
        cert.typeCode ??
        '';
    final body = (isAr ? cert.issuingBodyAr : cert.issuingBodyEn) ?? '';
    final dateText = cert.expiryDate == null
        ? '—'
        : (expired
            ? s.certExpiredOn(df.format(cert.expiryDate!))
            : s.certExpiresOn(df.format(cert.expiryDate!)));
    final accent = expired ? UiTokens.destructive : UiTokens.success;
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 6,
            height: 40,
            margin: const EdgeInsetsDirectional.only(end: 12),
            decoration: BoxDecoration(
              color: accent,
              borderRadius: BorderRadius.circular(3),
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                if (body.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    body,
                    style: TextStyle(color: UiTokens.muted, fontSize: 12),
                  ),
                ],
                const SizedBox(height: 6),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: accent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        expired ? s.certStatusExpired : s.certStatusValid,
                        style: TextStyle(
                          color: accent,
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.3,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        dateText,
                        style: TextStyle(
                          color: UiTokens.muted,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    if (cert.verified)
                      Icon(Icons.verified_outlined,
                          size: 16, color: UiTokens.muted),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _MedicalProfileCard extends StatelessWidget {
  const _MedicalProfileCard({required this.passport, required this.s});
  final WorkerPassport? passport;
  final AppStrings s;

  bool _hasAny(WorkerPassport p) {
    return (p.bloodType?.isNotEmpty ?? false) ||
        (p.allergies?.isNotEmpty ?? false) ||
        (p.chronicConditions?.isNotEmpty ?? false) ||
        (p.emergencyContactName?.isNotEmpty ?? false) ||
        (p.emergencyContactPhone?.isNotEmpty ?? false) ||
        p.medicalFitness != null;
  }

  @override
  Widget build(BuildContext context) {
    final p = passport;
    if (p == null || !_hasAny(p)) {
      return _EmptyCard(label: s.noMedicalProfile);
    }
    final df = DateFormat.yMd(s.locale.toLanguageTag());
    final rows = <_KV>[
      _KV(s.bloodTypeLabel, p.bloodType),
      _KV(s.allergiesLabel, p.allergies),
      _KV(s.chronicConditionsLabel, p.chronicConditions),
    ];
    final fitness = p.medicalFitness;
    if (fitness != null) {
      final v = fitness.validUntil;
      final label = fitness.isCurrentlyFit
          ? (v == null ? s.certStatusValid : s.certExpiresOn(df.format(v)))
          : s.certStatusExpired;
      rows.add(_KV(s.medicalFitnessLabel, label));
    }
    final contactName = p.emergencyContactName;
    final contactPhone = p.emergencyContactPhone;
    if ((contactName?.isNotEmpty ?? false) ||
        (contactPhone?.isNotEmpty ?? false)) {
      final parts = <String>[
        if (contactName?.isNotEmpty ?? false) contactName!,
        if (contactPhone?.isNotEmpty ?? false) contactPhone!,
      ];
      rows.add(_KV(s.emergencyContactLabel, parts.join(' • ')));
    }

    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          for (var i = 0; i < rows.length; i++) ...[
            if (i > 0)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Container(height: 1, color: UiTokens.border),
              ),
            _MedicalRow(label: rows[i].label, value: rows[i].value ?? '—'),
          ],
        ],
      ),
    );
  }
}

class _KV {
  final String label;
  final String? value;
  _KV(this.label, this.value);
}

class _MedicalRow extends StatelessWidget {
  const _MedicalRow({required this.label, required this.value});
  final String label;
  final String value;
  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          flex: 2,
          child: Text(
            label,
            style: TextStyle(
              color: UiTokens.muted,
              fontSize: 12,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          flex: 3,
          child: Text(
            value,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}

class _EmptyCard extends StatelessWidget {
  const _EmptyCard({required this.label});
  final String label;
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(10),
      ),
      alignment: Alignment.center,
      child: Text(
        label,
        textAlign: TextAlign.center,
        style: TextStyle(color: UiTokens.muted, fontSize: 13),
      ),
    );
  }
}
