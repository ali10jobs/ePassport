import 'package:flutter/material.dart';

import '../../api/models.dart';
import '../../theme/app_theme.dart';

/// Full-bleed result screen — green / red / amber depending on result.
/// Mirrors the web ScanResultScreen exactly so a security guard can use
/// either device interchangeably.
class ScanResultOverlay extends StatelessWidget {
  final ScanResult result;
  final VoidCallback onDismiss;

  const ScanResultOverlay({super.key, required this.result, required this.onDismiss});

  Color _bgColor() {
    switch (result.result) {
      case ScanResultStatus.green:
        return AppTokens.success;
      case ScanResultStatus.red:
        return AppTokens.destructive;
      case ScanResultStatus.impersonationFlag:
        return AppTokens.warning;
    }
  }

  String _heading() {
    switch (result.result) {
      case ScanResultStatus.green:
        return 'CLEAR — entry permitted';
      case ScanResultStatus.red:
        return 'STOP — entry denied';
      case ScanResultStatus.impersonationFlag:
        return 'IMPERSONATION FLAGGED';
    }
  }

  @override
  Widget build(BuildContext context) {
    final bg = _bgColor();
    return Scaffold(
      backgroundColor: bg,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 8),
              Text(
                _heading(),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.w600,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                result.subjectType == null
                    ? 'Unknown QR — no subject in our records.'
                    : '${result.subjectType?.toUpperCase()} • ${result.subjectId ?? '—'}',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.85),
                  fontFamily: 'monospace',
                  fontSize: 13,
                ),
              ),

              const SizedBox(height: 24),
              if (result.reasons.isNotEmpty)
                Expanded(
                  child: ListView.separated(
                    itemCount: result.reasons.length,
                    separatorBuilder: (_, __) =>
                        Divider(color: Colors.white.withValues(alpha: 0.18), height: 1),
                    itemBuilder: (_, i) {
                      final r = result.reasons[i];
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              r.code,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontFamily: 'monospace',
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            if (r.details != null) ...[
                              const SizedBox(height: 2),
                              Text(
                                _detailsLine(r.details!),
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.85),
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ],
                        ),
                      );
                    },
                  ),
                )
              else
                const Spacer(),

              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: bg,
                    minimumSize: const Size(0, 52),
                  ),
                  onPressed: onDismiss,
                  child: const Text('Next scan',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _detailsLine(Map<String, dynamic> d) {
    final entries = d.entries
        .where((e) => e.value != null)
        .map((e) => '${e.key}: ${e.value}')
        .toList();
    return entries.join(' · ');
  }
}
