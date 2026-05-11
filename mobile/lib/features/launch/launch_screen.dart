import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../shared/i18n.dart';
import '../../shared/preferences.dart';
import '../../shared/ui_tokens.dart';

/// Public landing — matches MobileScreens/stitch/launch_screen_updated.
/// Two entry points: Login (authenticated app) and anonymous hazard
/// report (no account required).
class LaunchScreen extends ConsumerWidget {
  const LaunchScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      backgroundColor: UiTokens.bg,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 8),
              const _LaunchHeader(),
              Expanded(
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        s.appTitle,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: UiTokens.ink,
                          fontSize: 32,
                          fontWeight: FontWeight.w700,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        s.launchSubtitle,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: UiTokens.muted,
                          fontSize: 15,
                          height: 1.4,
                        ),
                      ),
                      const SizedBox(height: 32),
                      _PillButton.primary(
                        label: s.login,
                        trailing: const Icon(Icons.arrow_forward, size: 18),
                        onPressed: () => context.push('/login'),
                      ),
                      const SizedBox(height: 12),
                      _PillButton.secondary(
                        label: s.reportHazardAnonymously,
                        leading:
                            const Icon(Icons.warning_amber_rounded, size: 18),
                        onPressed: () => context.push('/hazard/new'),
                      ),
                      const SizedBox(height: 20),
                      Text(
                        s.launchFooterBlurb,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: UiTokens.muted,
                          fontSize: 13,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const _EncryptionFooter(),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
    );
  }
}

class _LaunchHeader extends ConsumerWidget {
  const _LaunchHeader();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Row(
      children: [
        Text(
          'v2.4.0',
          style: TextStyle(
            color: UiTokens.muted,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
        const Spacer(),
        const _LanguagePill(),
      ],
    );
  }
}

class _LanguagePill extends ConsumerWidget {
  const _LanguagePill();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final prefs = ref.watch(appPreferencesProvider);
    final s = ref.watch(stringsProvider);
    final isAr = prefs.locale.languageCode == 'ar';
    return GestureDetector(
      onTap: () => ref
          .read(appPreferencesProvider.notifier)
          .setLocale(Locale(isAr ? 'en' : 'ar')),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: UiTokens.surface,
          border: Border.all(color: UiTokens.border),
          borderRadius: BorderRadius.circular(100),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.language, size: 14, color: UiTokens.ink),
            const SizedBox(width: 6),
            Text(
              isAr ? s.arabic : s.english,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(width: 4),
            Icon(Icons.keyboard_arrow_down, size: 16, color: UiTokens.ink),
          ],
        ),
      ),
    );
  }
}

class _EncryptionFooter extends ConsumerWidget {
  const _EncryptionFooter();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.shield_outlined, size: 14, color: UiTokens.muted),
        const SizedBox(width: 6),
        Text(
          s.encryptionActive,
          style: TextStyle(
            color: UiTokens.muted,
            fontSize: 11,
            fontWeight: FontWeight.w600,
            letterSpacing: 1.0,
          ),
        ),
      ],
    );
  }
}

/// Pill-shaped action button. 48px tall to satisfy gloved-use hit-target
/// minimum from MobileScreens/stitch/field_ops_monochrome/DESIGN.md.
class _PillButton extends StatelessWidget {
  const _PillButton.primary({
    required this.label,
    required this.onPressed,
    this.trailing,
  })  : _primary = true,
        leading = null;

  const _PillButton.secondary({
    required this.label,
    required this.onPressed,
    this.leading,
  })  : _primary = false,
        trailing = null;

  final String label;
  final VoidCallback onPressed;
  final Widget? leading;
  final Widget? trailing;
  final bool _primary;

  @override
  Widget build(BuildContext context) {
    final fg = _primary ? UiTokens.inkInverse : UiTokens.ink;
    final bg = _primary ? UiTokens.ink : UiTokens.surface;
    return SizedBox(
      height: 52,
      child: Material(
        color: bg,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(100),
          side: _primary
              ? BorderSide.none
              : BorderSide(color: UiTokens.border),
        ),
        child: InkWell(
          onTap: onPressed,
          customBorder: const StadiumBorder(),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (leading != null) ...[
                  IconTheme(data: IconThemeData(color: fg), child: leading!),
                  const SizedBox(width: 8),
                ],
                Text(
                  label,
                  style: TextStyle(
                    color: fg,
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                if (trailing != null) ...[
                  const SizedBox(width: 8),
                  IconTheme(data: IconThemeData(color: fg), child: trailing!),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
