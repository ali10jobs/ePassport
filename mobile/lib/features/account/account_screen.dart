import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../routing/auth_state.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// Account screen — security / privacy / password reset list, plus a
/// destructive Sign out at the bottom. Each row currently opens a
/// placeholder sheet; password reset wires to the existing forgot-flow
/// once the backend endpoint lands.
class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final strings = ref.watch(stringsProvider);
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final ink = isDark ? Colors.white : UiTokens.ink;
    final mutedText = isDark ? const Color(0xFFA3A3A3) : UiTokens.muted;
    final cardBg = isDark ? const Color(0xFF141414) : UiTokens.surface;
    final borderColor = isDark ? const Color(0xFF1F1F1F) : UiTokens.border;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      body: SafeArea(
        child: Column(
          children: [
            _Header(title: strings.account, ink: ink, borderColor: borderColor),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                children: [
                  _SectionLabel(text: strings.preferences, color: mutedText),
                  const SizedBox(height: 8),
                  _AccountTile(
                    icon: Icons.shield_outlined,
                    title: strings.security,
                    subtitle: strings.securitySubtitle,
                    onTap: () => _showPlaceholder(context, strings.security, strings),
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 10),
                  _AccountTile(
                    icon: Icons.privacy_tip_outlined,
                    title: strings.privacy,
                    subtitle: strings.privacySubtitle,
                    onTap: () => _showPlaceholder(context, strings.privacy, strings),
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 10),
                  _AccountTile(
                    icon: Icons.lock_reset_outlined,
                    title: strings.passwordReset,
                    subtitle: strings.passwordResetSubtitle,
                    onTap: () => _showPasswordReset(context, ref, strings),
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    height: 52,
                    child: Material(
                      color: UiTokens.danger,
                      shape: const StadiumBorder(),
                      child: InkWell(
                        customBorder: const StadiumBorder(),
                        onTap: () => _confirmSignOut(context, ref, strings),
                        child: Center(
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.logout,
                                  color: Colors.white, size: 18),
                              const SizedBox(width: 8),
                              Text(
                                strings.signOut.toUpperCase(),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 14,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.0,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showPlaceholder(BuildContext context, String title, AppStrings s) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('$title — ${s.comingSoon}')),
    );
  }

  Future<void> _showPasswordReset(
    BuildContext context,
    WidgetRef ref,
    AppStrings s,
  ) async {
    final email = ref.read(authControllerProvider).user?.email ?? '';
    final theme = Theme.of(context);
    showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(s.sendResetLink),
        content: Text(
          s.resetLinkBody(email),
          style: theme.textTheme.bodyMedium,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text(s.cancel),
          ),
          FilledButton(
            onPressed: () {
              Navigator.of(context).pop();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(s.resetLinkSent)),
              );
            },
            child: Text(s.send),
          ),
        ],
      ),
    );
  }

  Future<void> _confirmSignOut(
    BuildContext context,
    WidgetRef ref,
    AppStrings strings,
  ) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(strings.signOut),
        content: Text(strings.signOutPrompt),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(strings.cancel),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: UiTokens.danger),
            onPressed: () => Navigator.of(context).pop(true),
            child: Text(strings.signOut),
          ),
        ],
      ),
    );
    if (ok == true) {
      await ref.read(authControllerProvider.notifier).signOut();
    }
  }
}

class _Header extends StatelessWidget {
  const _Header({
    required this.title,
    required this.ink,
    required this.borderColor,
  });
  final String title;
  final Color ink;
  final Color borderColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: borderColor)),
      ),
      padding: const EdgeInsetsDirectional.fromSTEB(8, 8, 16, 8),
      child: Row(
        children: [
          IconButton(
            icon: Icon(Icons.arrow_back, color: ink),
            onPressed: () => context.canPop()
                ? context.pop()
                : context.go('/dashboard'),
          ),
          Text(
            title,
            style: TextStyle(
              color: ink,
              fontSize: 18,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.3,
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel({required this.text, required this.color});
  final String text;
  final Color color;
  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: TextStyle(
        color: color,
        fontSize: 11,
        fontWeight: FontWeight.w800,
        letterSpacing: 1.2,
      ),
    );
  }
}

class _AccountTile extends StatelessWidget {
  const _AccountTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
    required this.ink,
    required this.mutedText,
    required this.cardBg,
    required this.borderColor,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final Color ink;
  final Color mutedText;
  final Color cardBg;
  final Color borderColor;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: cardBg,
      borderRadius: BorderRadius.circular(8),
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.fromLTRB(14, 14, 14, 14),
          decoration: BoxDecoration(
            border: Border.all(color: borderColor),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: Theme.of(context).brightness == Brightness.dark
                      ? const Color(0xFF1A1A1A)
                      : UiTokens.surfaceMuted,
                  borderRadius: BorderRadius.circular(6),
                ),
                alignment: Alignment.center,
                child: Icon(icon, color: ink, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title.toUpperCase(),
                      style: TextStyle(
                        color: ink,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.6,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: mutedText,
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.arrow_forward, color: ink, size: 18),
            ],
          ),
        ),
      ),
    );
  }
}
