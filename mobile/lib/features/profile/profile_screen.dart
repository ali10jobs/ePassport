import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../routing/auth_state.dart';
import '../../shared/i18n.dart';
import '../../shared/preferences.dart';
import '../../shared/ui_tokens.dart';

/// Profile screen — editable photo + bio.
/// Name and job title (organization role) are read-only — those flow
/// from the HR system. Edits persist locally via AppPreferences until
/// the backend grows a /me PATCH endpoint.
class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  late final TextEditingController _bioCtrl;
  bool _initialized = false;

  @override
  void initState() {
    super.initState();
    _bioCtrl = TextEditingController();
  }

  @override
  void dispose() {
    _bioCtrl.dispose();
    super.dispose();
  }

  String _workerKey() => ref.read(authControllerProvider).user?.id.toString() ?? 'guest';

  Future<void> _pickPhoto() async {
    final picker = ImagePicker();
    final file = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
      maxWidth: 1024,
    );
    if (file == null) return;
    await ref
        .read(appPreferencesProvider.notifier)
        .setAvatarPath(_workerKey(), file.path);
  }

  Future<void> _save() async {
    await ref
        .read(appPreferencesProvider.notifier)
        .setBio(_workerKey(), _bioCtrl.text.trim());
    if (!mounted) return;
    final s = ref.read(stringsProvider);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(s.profileSaved)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final prefs = ref.watch(appPreferencesProvider);
    final strings = ref.watch(stringsProvider);
    final user = auth.user;
    final key = _workerKey();

    if (!_initialized) {
      _bioCtrl.text = prefs.bios[key] ?? '';
      _initialized = true;
    }

    final avatarPath = prefs.avatarPaths[key];
    final jobTitle = user?.organizations.isNotEmpty == true
        ? user!.organizations.first.role
        : '—';

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
            _Header(title: strings.profile, ink: ink, borderColor: borderColor),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                children: [
                  Center(
                    child: Column(
                      children: [
                        GestureDetector(
                          onTap: _pickPhoto,
                          child: Container(
                            width: 112,
                            height: 112,
                            decoration: BoxDecoration(
                              color: UiTokens.inkSolid,
                              borderRadius: BorderRadius.circular(12),
                              image: avatarPath != null
                                  ? DecorationImage(
                                      image: FileImage(File(avatarPath)),
                                      fit: BoxFit.cover,
                                    )
                                  : null,
                            ),
                            alignment: Alignment.center,
                            child: avatarPath == null
                                ? const Icon(Icons.person,
                                    color: Colors.white, size: 48)
                                : null,
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextButton.icon(
                          onPressed: _pickPhoto,
                          icon: Icon(Icons.photo_camera_outlined,
                              size: 16, color: ink),
                          label: Text(
                            strings.changePhoto.toUpperCase(),
                            style: TextStyle(
                              color: ink,
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  _ReadonlyRow(
                    label: strings.name,
                    value: user?.name ?? '—',
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 10),
                  _ReadonlyRow(
                    label: strings.jobTitle,
                    value: jobTitle,
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 10),
                  _ReadonlyRow(
                    label: strings.email,
                    value: user?.email ?? '—',
                    ink: ink,
                    mutedText: mutedText,
                    cardBg: cardBg,
                    borderColor: borderColor,
                  ),
                  const SizedBox(height: 20),
                  Text(
                    strings.bio.toUpperCase(),
                    style: TextStyle(
                      color: mutedText,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.2,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _bioCtrl,
                    minLines: 3,
                    maxLines: 6,
                    maxLength: 240,
                    style: TextStyle(color: ink, fontSize: 14),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: cardBg,
                      hintText: strings.bioHint,
                      hintStyle: TextStyle(color: mutedText),
                      contentPadding: const EdgeInsets.all(14),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(6),
                        borderSide: BorderSide(color: borderColor),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(6),
                        borderSide: BorderSide(color: ink, width: 1.5),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    height: 52,
                    child: Material(
                      color: ink,
                      shape: const StadiumBorder(),
                      child: InkWell(
                        customBorder: const StadiumBorder(),
                        onTap: _save,
                        child: Center(
                          child: Text(
                            strings.save.toUpperCase(),
                            style: TextStyle(
                              color: isDark ? Colors.black : Colors.white,
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 1.0,
                            ),
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

class _ReadonlyRow extends StatelessWidget {
  const _ReadonlyRow({
    required this.label,
    required this.value,
    required this.ink,
    required this.mutedText,
    required this.cardBg,
    required this.borderColor,
  });
  final String label;
  final String value;
  final Color ink;
  final Color mutedText;
  final Color cardBg;
  final Color borderColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
      decoration: BoxDecoration(
        color: cardBg,
        border: Border.all(color: borderColor),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label.toUpperCase(),
                  style: TextStyle(
                    color: mutedText,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.0,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    color: ink,
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          Icon(Icons.lock_outline, size: 16, color: mutedText),
        ],
      ),
    );
  }
}
