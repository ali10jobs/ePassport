import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../api/api_exception.dart';
import '../../routing/auth_state.dart';
import '../../shared/i18n.dart';
import '../../shared/preferences.dart';
import '../../shared/ui_tokens.dart';

/// System Access — matches MobileScreens/stitch/login_screen_updated_header.
/// Card-on-canvas layout, monochrome palette, pill-shaped primary action.
class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});
  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _onSubmit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _submitting = true;
      _error = null;
    });
    final s = ref.read(stringsProvider);
    try {
      await ref.read(authControllerProvider.notifier).signIn(
            email: _emailCtrl.text.trim(),
            password: _passwordCtrl.text,
          );
    } on ApiException catch (e) {
      setState(() => _error =
          e.code == ErrorCodes.unauthenticated ? s.loginFailed : e.message);
    } catch (e, st) {
      // Surface the underlying exception for in-field diagnosis. The previous
      // generic "could not sign in" hid Dio/Socket/Handshake errors.
      setState(() => _error =
          '${s.loginFailed}: ${e.runtimeType} ${e.toString().split('\n').first}');
      // ignore: avoid_print
      print('login error: $e\n$st');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      backgroundColor: UiTokens.bg,
      body: SafeArea(
        child: Column(
          children: [
            _Header(
              onBack: () =>
                  context.canPop() ? context.pop() : context.go('/launch'),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      s.appTitle,
                      style: TextStyle(
                        color: UiTokens.ink,
                        fontSize: 30,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 20),
                    _Card(child: _buildForm(s)),
                    const SizedBox(height: 32),
                    Center(
                      child: GestureDetector(
                        onTap: () {},
                        child: Text(
                          s.termsAcknowledgement,
                          style: TextStyle(
                            color: UiTokens.ink,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Center(
                      child: Text(
                        'v2.4.0-STABLE',
                        style: TextStyle(
                          color: UiTokens.muted,
                          fontSize: 11,
                          letterSpacing: 1.0,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildForm(AppStrings s) {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            s.systemAccess,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 22,
              fontWeight: FontWeight.w700,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            s.secureSessionBlurb,
            style: TextStyle(color: UiTokens.muted, fontSize: 14, height: 1.5),
          ),
          const SizedBox(height: 24),
          _FieldLabel(s.emailLabel),
          const SizedBox(height: 8),
          _StyledField(
            controller: _emailCtrl,
            hint: 'engineer@epassport.io',
            keyboardType: TextInputType.emailAddress,
            autofillHints: const [AutofillHints.email],
            enabled: !_submitting,
            validator: (v) =>
                (v == null || !v.contains('@')) ? s.loginFailed : null,
          ),
          const SizedBox(height: 16),
          _FieldLabel(s.passwordLabel),
          const SizedBox(height: 8),
          _StyledField(
            controller: _passwordCtrl,
            hint: '••••••••',
            obscureText: true,
            autofillHints: const [AutofillHints.password],
            enabled: !_submitting,
            validator: (v) => (v == null || v.isEmpty) ? s.loginFailed : null,
          ),
          if (_error != null) ...[
            const SizedBox(height: 12),
            _ErrorBanner(message: _error!),
          ],
          const SizedBox(height: 20),
          _SignInButton(
            label: _submitting ? '…' : s.signIn,
            submitting: _submitting,
            onPressed: _submitting ? null : _onSubmit,
          ),
          const SizedBox(height: 24),
          Divider(color: UiTokens.border, height: 1),
          const SizedBox(height: 20),
          _SecureSessionCallout(
            title: s.secureSession,
            subtitle: s.secureSessionBlurb,
          ),
        ],
      ),
    );
  }
}

class _Header extends ConsumerWidget {
  const _Header({required this.onBack});
  final VoidCallback onBack;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: UiTokens.border)),
      ),
      padding: const EdgeInsetsDirectional.fromSTEB(12, 8, 16, 12),
      child: Row(
        children: [
          IconButton(
            onPressed: onBack,
            icon: Icon(Icons.arrow_back, color: UiTokens.ink),
            iconSize: 22,
            splashRadius: 22,
          ),
          const Spacer(),
          const _LanguagePill(),
        ],
      ),
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

class _Card extends StatelessWidget {
  const _Card({required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: child,
    );
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text.toUpperCase(),
      style: TextStyle(
        color: UiTokens.ink,
        fontSize: 11,
        fontWeight: FontWeight.w700,
        letterSpacing: 1.2,
      ),
    );
  }
}

class _StyledField extends StatelessWidget {
  const _StyledField({
    required this.controller,
    required this.hint,
    this.keyboardType,
    this.autofillHints,
    this.obscureText = false,
    this.enabled = true,
    this.validator,
  });

  final TextEditingController controller;
  final String hint;
  final TextInputType? keyboardType;
  final Iterable<String>? autofillHints;
  final bool obscureText;
  final bool enabled;
  final String? Function(String?)? validator;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      autofillHints: autofillHints,
      obscureText: obscureText,
      enabled: enabled,
      validator: validator,
      style: TextStyle(color: UiTokens.ink, fontSize: 15),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: UiTokens.muted, fontSize: 15),
        filled: true,
        fillColor: UiTokens.inputFill,
        isDense: true,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(6),
          borderSide: BorderSide(color: UiTokens.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(6),
          borderSide: BorderSide(color: UiTokens.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(6),
          borderSide: BorderSide(color: UiTokens.ink, width: 1.5),
        ),
      ),
    );
  }
}

class _SignInButton extends StatelessWidget {
  const _SignInButton({
    required this.label,
    required this.submitting,
    required this.onPressed,
  });
  final String label;
  final bool submitting;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 52,
      child: Material(
        color: UiTokens.ink,
        shape: const StadiumBorder(),
        child: InkWell(
          onTap: onPressed,
          customBorder: const StadiumBorder(),
          child: Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    color: UiTokens.inkInverse,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.0,
                  ),
                ),
                if (!submitting) ...[
                  const SizedBox(width: 8),
                  Icon(Icons.login, color: UiTokens.inkInverse, size: 16),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _SecureSessionCallout extends StatelessWidget {
  const _SecureSessionCallout({required this.title, required this.subtitle});
  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: UiTokens.surfaceMuted,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        children: [
          Icon(Icons.shield_outlined, color: UiTokens.ink, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    color: UiTokens.muted,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: scheme.error.withValues(alpha: 0.06),
        border: Border.all(color: scheme.error.withValues(alpha: 0.3)),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        message,
        style: TextStyle(color: scheme.error, fontSize: 13),
      ),
    );
  }
}
