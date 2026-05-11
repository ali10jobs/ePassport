import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../routing/auth_state.dart';
import 'i18n.dart';
import 'preferences.dart';
import 'ui_tokens.dart';

/// Right-side drawer surfaced by AppShell. Identity block at top,
/// Profile + Account rows, Settings pinned at the bottom.
class ProfileSidebar extends ConsumerWidget {
  const ProfileSidebar({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authControllerProvider);
    final prefs = ref.watch(appPreferencesProvider);
    final strings = ref.watch(stringsProvider);
    final user = auth.user;
    final workerKey = user?.id.toString() ?? 'guest';
    final avatarPath = prefs.avatarPaths[workerKey];
    final theme = Theme.of(context);

    return Drawer(
      backgroundColor: theme.scaffoldBackgroundColor,
      shape: const RoundedRectangleBorder(),
      child: SafeArea(
        child: Column(
          children: [
            _IdentityHeader(
              name: user?.name ?? '—',
              email: user?.email ?? '',
              avatarPath: avatarPath,
            ),
            const Divider(height: 1),
            Expanded(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  _SidebarRow(
                    icon: Icons.person_outline,
                    label: strings.profile,
                    onTap: () {
                      Navigator.of(context).pop();
                      context.go('/profile');
                    },
                  ),
                  _SidebarRow(
                    icon: Icons.shield_outlined,
                    label: strings.account,
                    onTap: () {
                      Navigator.of(context).pop();
                      context.go('/account');
                    },
                  ),
                ],
              ),
            ),
            const Divider(height: 1),
            _SidebarRow(
              icon: Icons.tune,
              label: strings.settings,
              onTap: () {
                Navigator.of(context).pop();
                context.go('/settings');
              },
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

class _IdentityHeader extends StatelessWidget {
  const _IdentityHeader({
    required this.name,
    required this.email,
    required this.avatarPath,
  });
  final String name;
  final String email;
  final String? avatarPath;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final ink = isDark ? Colors.white : UiTokens.ink;
    final mutedText = isDark ? const Color(0xFFA3A3A3) : UiTokens.muted;
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: UiTokens.inkSolid,
              borderRadius: BorderRadius.circular(8),
              image: avatarPath != null
                  ? DecorationImage(
                      image: FileImage(File(avatarPath!)),
                      fit: BoxFit.cover,
                    )
                  : null,
            ),
            alignment: Alignment.center,
            child: avatarPath == null
                ? const Icon(Icons.person, color: Colors.white, size: 24)
                : null,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: ink,
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  email,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: mutedText, fontSize: 12),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SidebarRow extends StatelessWidget {
  const _SidebarRow({
    required this.icon,
    required this.label,
    required this.onTap,
  });
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final ink = isDark ? Colors.white : UiTokens.ink;
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Row(
          children: [
            Icon(icon, size: 20, color: ink),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  color: ink,
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            Icon(Icons.chevron_right,
                size: 18,
                color: isDark
                    ? const Color(0xFFA3A3A3)
                    : UiTokens.muted),
          ],
        ),
      ),
    );
  }
}
