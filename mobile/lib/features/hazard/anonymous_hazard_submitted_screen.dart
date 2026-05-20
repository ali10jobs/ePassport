import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';
import 'hazards_screen.dart' show allHazardsProvider;

/// Hazard submitted — matches
/// MobileScreens/stitch/hazard_submission_success_annotated_refinements.
class AnonymousHazardSubmittedScreen extends ConsumerWidget {
  final String anonymousReportId;
  final String severity;
  final bool inAppShell;
  final List<Uint8List> photos;
  final String? reporterName;
  final bool isAnonymous;
  final String? category;

  const AnonymousHazardSubmittedScreen({
    super.key,
    required this.anonymousReportId,
    this.severity = 'high',
    this.inAppShell = false,
    this.photos = const [],
    this.reporterName,
    this.isAnonymous = true,
    this.category,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final body = _buildBody(context, ref, s, severity);
    if (inAppShell) {
      return AppShell(
        tab: AppTab.hazards,
        bodyPadding: EdgeInsets.zero,
        child: body,
      );
    }
    return Scaffold(
      backgroundColor: UiTokens.bg,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            Container(
              decoration: BoxDecoration(
                border: Border(bottom: BorderSide(color: UiTokens.border)),
              ),
              padding: const EdgeInsetsDirectional.fromSTEB(20, 12, 16, 12),
              child: Row(
                children: [
                  Text(
                    'ePassport',
                    style: TextStyle(
                      color: UiTokens.ink,
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(child: body),
          ],
        ),
      ),
    );
  }

  Widget _buildBody(
      BuildContext context, WidgetRef ref, AppStrings s, String severity) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      children: [
        _SuccessBanner(text: s.reportSubmittedTitle),
        const SizedBox(height: 20),
        _ReportIdCard(
          reportId: anonymousReportId,
          severity: severity,
          photos: photos,
          reporterName: reporterName,
          isAnonymous: isAnonymous,
          category: category,
          s: s,
        ),
        const SizedBox(height: 20),
        _PrimaryPill(
          label: s.returnToHazards,
          icon: Icons.warning_amber_rounded,
          onTap: () async {
            // Invalidate AND await the refetch before navigating — otherwise
            // the user lands on /hazards while the new fetch is still in
            // flight, and autoDispose hands them the previous cached list
            // (looks identical to "no refresh happened").
            if (inAppShell) {
              ref.invalidate(allHazardsProvider);
              try {
                await ref.read(allHazardsProvider.future);
              } catch (_) {
                // Swallow fetch errors here — the user can still pull-to-refresh
                // on the hazards screen if the network was momentarily down.
              }
            }
            if (context.mounted) {
              context.go(inAppShell ? '/hazards' : '/launch');
            }
          },
        ),
        const SizedBox(height: 10),
        Center(
          child: GestureDetector(
            onTap: () async {
              await Clipboard.setData(ClipboardData(text: anonymousReportId));
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(s.reportIdCopied)),
                );
              }
            },
            child: Text(
              s.copyReportId,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 13,
                fontWeight: FontWeight.w600,
                decoration: TextDecoration.underline,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _SuccessBanner extends StatelessWidget {
  const _SuccessBanner({required this.text});
  final String text;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
      decoration: BoxDecoration(
        color: UiTokens.success,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: const Icon(Icons.check, color: Colors.white, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w800,
                height: 1.25,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ReportIdCard extends StatelessWidget {
  const _ReportIdCard({
    required this.reportId,
    required this.severity,
    required this.photos,
    required this.reporterName,
    required this.isAnonymous,
    required this.category,
    required this.s,
  });
  final String reportId;
  final String severity;
  final List<Uint8List> photos;
  final String? reporterName;
  final bool isAnonymous;
  final String? category;
  final AppStrings s;

  @override
  Widget build(BuildContext context) {
    final short = reportId.length > 8 ? reportId.substring(0, 8) : reportId;
    return Container(
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 18),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Text(
              s.trackingReference,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 12,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
          ),
          const SizedBox(height: 14),
          Container(
            padding:
                const EdgeInsets.symmetric(vertical: 18, horizontal: 12),
            decoration: BoxDecoration(
              color: UiTokens.surfaceMuted,
              borderRadius: BorderRadius.circular(6),
            ),
            child: Center(
              child: SelectableText(
                s.reportIdLabel(short),
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.3,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          _PhotoGallery(photos: photos),
          const SizedBox(height: 16),
          _MetaLine(label: s.status, value: s.verified, valueColor: UiTokens.success),
          Divider(color: UiTokens.border, height: 24),
          _MetaLine(label: s.priority, value: s.priorityLabelFor(severity)),
          Divider(color: UiTokens.border, height: 24),
          _MetaLine(
            label: s.submittedBy,
            value: isAnonymous ? s.anonymous : (reporterName ?? s.anonymous),
          ),
          Divider(color: UiTokens.border, height: 24),
          _MetaLine(label: s.reference, value: '#H-$short'),
        ],
      ),
    );
  }
}

class _PhotoGallery extends StatelessWidget {
  const _PhotoGallery({required this.photos});
  final List<Uint8List> photos;

  @override
  Widget build(BuildContext context) {
    if (photos.isEmpty) {
      return AspectRatio(
        aspectRatio: 16 / 9,
        child: Container(
          decoration: BoxDecoration(
            color: UiTokens.surfaceMuted,
            borderRadius: BorderRadius.circular(6),
            border: Border.all(color: UiTokens.border),
          ),
          alignment: Alignment.center,
          child: Icon(Icons.image_outlined, color: UiTokens.muted, size: 36),
        ),
      );
    }
    if (photos.length == 1) {
      return AspectRatio(
        aspectRatio: 16 / 9,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: Image.memory(photos.first, fit: BoxFit.cover),
        ),
      );
    }
    return SizedBox(
      height: 110,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: photos.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          return ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: AspectRatio(
              aspectRatio: 1,
              child: Image.memory(photos[i], fit: BoxFit.cover),
            ),
          );
        },
      ),
    );
  }
}

class _MetaLine extends StatelessWidget {
  _MetaLine({
    required this.label,
    required this.value,
    this.valueColor,
  });
  final String label;
  final String value;
  final Color? valueColor;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(label,
            style: TextStyle(color: UiTokens.muted, fontSize: 13)),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            color: valueColor ?? UiTokens.ink,
            fontSize: 13,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.5,
          ),
        ),
      ],
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
  final VoidCallback onTap;

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
                Icon(icon, size: 18, color: Colors.white),
                const SizedBox(width: 8),
                Text(
                  label,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
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
