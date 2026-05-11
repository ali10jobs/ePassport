import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// Placeholder for the Permits tab — no stitch mockup yet.
/// Will become the permit list once the API + UX are finalised.
class PermitsScreen extends ConsumerWidget {
  const PermitsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return AppShell(
      tab: AppTab.permits,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: UiTokens.surfaceMuted,
                  borderRadius: BorderRadius.circular(10),
                ),
                alignment: Alignment.center,
                child: Icon(Icons.assignment_outlined,
                    color: UiTokens.ink, size: 26),
              ),
              const SizedBox(height: 14),
              Text(
                s.tabPermits,
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                s.comingSoon,
                textAlign: TextAlign.center,
                style: TextStyle(color: UiTokens.muted, fontSize: 13),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
