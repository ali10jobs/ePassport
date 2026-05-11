import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../shared/i18n.dart';
import '../../shared/preferences.dart';
import '../../shared/ui_tokens.dart';

/// Settings — language + appearance. Persists through AppPreferences
/// (secure storage) so the choice survives cold-start.
class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final prefs = ref.watch(appPreferencesProvider);
    final ctrl = ref.read(appPreferencesProvider.notifier);
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
            _Header(title: strings.settings, ink: ink, borderColor: borderColor),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                children: [
                  _SectionLabel(text: strings.language.toUpperCase(), color: mutedText),
                  const SizedBox(height: 8),
                  _SegmentedRow<Locale>(
                    cardBg: cardBg,
                    borderColor: borderColor,
                    ink: ink,
                    mutedText: mutedText,
                    value: prefs.locale,
                    onChanged: (l) => ctrl.setLocale(l),
                    options: [
                      _Seg(const Locale('en'), strings.english),
                      _Seg(const Locale('ar'), strings.arabic),
                    ],
                  ),
                  const SizedBox(height: 24),
                  _SectionLabel(text: strings.appearance.toUpperCase(), color: mutedText),
                  const SizedBox(height: 8),
                  _SegmentedRow<ThemeMode>(
                    cardBg: cardBg,
                    borderColor: borderColor,
                    ink: ink,
                    mutedText: mutedText,
                    value: prefs.themeMode,
                    onChanged: (m) => ctrl.setThemeMode(m),
                    options: [
                      _Seg(ThemeMode.light, strings.light),
                      _Seg(ThemeMode.dark, strings.dark),
                      _Seg(ThemeMode.system, strings.system),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
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
      padding: const EdgeInsets.fromLTRB(8, 8, 16, 8),
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

class _Seg<T> {
  final T value;
  final String label;
  const _Seg(this.value, this.label);
}

class _SegmentedRow<T> extends StatelessWidget {
  const _SegmentedRow({
    required this.value,
    required this.options,
    required this.onChanged,
    required this.cardBg,
    required this.borderColor,
    required this.ink,
    required this.mutedText,
  });
  final T value;
  final List<_Seg<T>> options;
  final ValueChanged<T> onChanged;
  final Color cardBg;
  final Color borderColor;
  final Color ink;
  final Color mutedText;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: cardBg,
        border: Border.all(color: borderColor),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: options.map((opt) {
          final selected = opt.value == value;
          return Expanded(
            child: GestureDetector(
              onTap: selected ? null : () => onChanged(opt.value),
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 2),
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: selected ? ink : Colors.transparent,
                  borderRadius: BorderRadius.circular(6),
                ),
                alignment: Alignment.center,
                child: Text(
                  opt.label,
                  style: TextStyle(
                    color: selected
                        ? (Theme.of(context).brightness == Brightness.dark
                            ? Colors.black
                            : Colors.white)
                        : mutedText,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}
