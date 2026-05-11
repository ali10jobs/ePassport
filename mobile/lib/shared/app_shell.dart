import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../routing/auth_state.dart';
import 'i18n.dart';
import 'preferences.dart';
import 'profile_sidebar.dart';
import 'ui_tokens.dart';

/// 4-tab bottom-nav shell shared across authenticated screens.
/// Matches MobileScreens/stitch/dashboard_navigation_aligned (bottom bar).
/// Right-side endDrawer surfaces the profile sidebar.
enum AppTab { dashboard, scan, permits, hazards }

class AppShell extends StatelessWidget {
  const AppShell({
    super.key,
    required this.tab,
    required this.child,
    this.showHeader = true,
    this.headerAction,
    this.onBack,
    this.backgroundColor,
    this.bodyPadding,
  });

  final AppTab tab;
  final Widget child;
  final bool showHeader;
  final Widget? headerAction;
  final VoidCallback? onBack;
  final Color? backgroundColor;
  final EdgeInsetsGeometry? bodyPadding;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: backgroundColor ?? UiTokens.bg,
      endDrawer: const ProfileSidebar(),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            if (showHeader) AppHeader(action: headerAction, onBack: onBack),
            Expanded(
              child: Padding(
                padding: bodyPadding ?? EdgeInsets.zero,
                child: child,
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: _BottomNav(active: tab),
    );
  }
}

class AppHeader extends StatelessWidget {
  const AppHeader({super.key, this.action, this.onBack});
  final Widget? action;
  final VoidCallback? onBack;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: UiTokens.border)),
      ),
      padding: const EdgeInsetsDirectional.fromSTEB(8, 12, 16, 12),
      child: Row(
        children: [
          if (onBack != null)
            IconButton(
              onPressed: onBack,
              icon: Icon(Icons.arrow_back, color: UiTokens.ink, size: 22),
              tooltip: MaterialLocalizations.of(context).backButtonTooltip,
              padding: const EdgeInsets.all(8),
              constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
            )
          else
            const SizedBox(width: 12),
          Text(
            'ePassport',
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 22,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.5,
            ),
          ),
          const Spacer(),
          if (action != null) ...[action!, const SizedBox(width: 8)],
          const _AvatarButton(),
        ],
      ),
    );
  }
}

class _AvatarButton extends ConsumerWidget {
  const _AvatarButton();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authControllerProvider);
    final prefs = ref.watch(appPreferencesProvider);
    final workerKey = auth.user?.id.toString() ?? 'guest';
    final path = prefs.avatarPaths[workerKey];
    return InkWell(
      borderRadius: BorderRadius.circular(6),
      onTap: () => Scaffold.of(context).openEndDrawer(),
      child: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: UiTokens.inkSolid,
          borderRadius: BorderRadius.circular(6),
          image: path != null
              ? DecorationImage(image: FileImage(File(path)), fit: BoxFit.cover)
              : null,
        ),
        alignment: Alignment.center,
        child: path == null
            ? const Icon(Icons.person, color: Colors.white, size: 20)
            : null,
      ),
    );
  }
}

class _BottomNav extends ConsumerWidget {
  const _BottomNav({required this.active});
  final AppTab active;

  static const _tabs = <_TabSpec>[
    _TabSpec(AppTab.dashboard, Icons.dashboard_outlined, '/dashboard'),
    _TabSpec(AppTab.scan, Icons.qr_code_scanner, '/scan'),
    _TabSpec(AppTab.permits, Icons.assignment_outlined, '/permits'),
    _TabSpec(AppTab.hazards, Icons.warning_amber_rounded, '/hazards'),
  ];

  String _labelFor(AppTab tab, AppStrings s) {
    return switch (tab) {
      AppTab.dashboard => s.tabDashboard,
      AppTab.scan => s.tabScan,
      AppTab.permits => s.tabPermits,
      AppTab.hazards => s.tabHazards,
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Container(
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border(top: BorderSide(color: UiTokens.border)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          child: Row(
            children: _tabs.map((t) {
              final selected = t.tab == active;
              return Expanded(
                child: InkWell(
                  borderRadius: BorderRadius.circular(8),
                  onTap: selected ? null : () => context.go(t.route),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 6),
                          decoration: BoxDecoration(
                            color: selected ? UiTokens.inkSolid : Colors.transparent,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Icon(
                            t.icon,
                            size: 18,
                            color: selected ? Colors.white : UiTokens.muted,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _labelFor(t.tab, s),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: selected ? UiTokens.ink : UiTokens.muted,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ),
    );
  }
}

class _TabSpec {
  final AppTab tab;
  final IconData icon;
  final String route;
  const _TabSpec(this.tab, this.icon, this.route);
}
