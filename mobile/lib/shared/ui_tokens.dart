import 'package:flutter/material.dart';

/// Mockup-aligned palette (MobileScreens/stitch/field_ops_monochrome).
/// Brightness-aware: every screen that references `UiTokens.X` adapts
/// automatically when the user toggles dark mode in Settings.
///
/// The active brightness is set globally from the root widget via
/// `UiTokens.applyBrightness()`. A MaterialApp rebuild after a theme
/// change re-evaluates every descendant, so widget-level swaps happen
/// in the same frame as the toggle.
class UiTokens {
  static Brightness _brightness = Brightness.light;
  static bool get _dark => _brightness == Brightness.dark;

  static void applyBrightness(Brightness b) {
    _brightness = b;
  }

  // Surfaces ----------------------------------------------------------
  static Color get bg => _dark ? const Color(0xFF0A0A0A) : const Color(0xFFF9F9F9);
  static Color get surface => _dark ? const Color(0xFF141414) : const Color(0xFFFFFFFF);
  static Color get surfaceMuted => _dark ? const Color(0xFF1A1A1A) : const Color(0xFFF1F1F1);
  static Color get inputFill => _dark ? const Color(0xFF1A1A1A) : const Color(0xFFF5F5F5);
  static Color get border => _dark ? const Color(0xFF1F1F1F) : const Color(0xFFEAEAEA);

  // Foreground -------------------------------------------------------
  /// Primary text/icon color. Flips between black ink and near-white.
  static Color get ink => _dark ? const Color(0xFFFAFAFA) : const Color(0xFF0A0A0A);
  /// Inverse of ink — useful for solid-ink buttons whose label must
  /// contrast (white-on-black in light, black-on-white in dark).
  static Color get inkInverse => _dark ? const Color(0xFF0A0A0A) : const Color(0xFFFFFFFF);
  /// Stays dark in both themes. Use for "inverted" blocks that must
  /// look like a near-black card with white text in light AND dark —
  /// e.g. the Organization card on the dashboard and the selected
  /// bottom-nav pill. In dark mode it sits slightly above the page
  /// background so the block stays visible against #0A0A0A.
  static Color get inkSolid => _dark ? const Color(0xFF262626) : const Color(0xFF0A0A0A);
  static Color get muted => _dark ? const Color(0xFFA3A3A3) : const Color(0xFF737373);
  static Color get mutedStrong => _dark ? const Color(0xFFD4D4D4) : const Color(0xFF404040);

  // Signal colors stay vivid in both modes — gate scan + emergency UI
  // must read identically under any brightness.
  static Color get success => const Color(0xFF16A34A);
  static Color get successSoft =>
      _dark ? const Color(0xFF14532D) : const Color(0xFFDCFCE7);
  static Color get destructive => const Color(0xFFDC2626);
  static Color get destructiveSoft =>
      _dark ? const Color(0xFF7F1D1D) : const Color(0xFFFEE2E2);
  static Color get danger => const Color(0xFFB91C1C);
  static Color get warning => const Color(0xFFF59E0B);
}
