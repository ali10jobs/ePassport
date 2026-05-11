import 'package:flutter/material.dart';

/// Material 3 theme that mirrors the web's Vercel-style tokens:
///   --radius = 4px (no playful curvature anywhere)
///   neutral palette + a single brand accent
///   semantic status colors stay vivid (green / red / amber)
///
/// All radii, spacing, and color values flow through ColorScheme +
/// AppTokens so re-theming = edit this file only.
class AppTokens {
  static const double radius = 4.0;
  static const double radiusSmall = 2.0;
  static const double radiusLarge = 6.0;

  // Status colors — must stay vivid for gate scan + hard-block screens.
  static const Color success = Color(0xFF059669); // emerald-600
  static const Color destructive = Color(0xFFDC2626); // red-600
  static const Color warning = Color(0xFFF59E0B); // amber-500
  static const Color primary = Color(0xFF0070F3); // vercel-blue
}

ThemeData buildAppTheme() => _buildTheme(Brightness.light);
ThemeData buildDarkTheme() => _buildTheme(Brightness.dark);

ThemeData _buildTheme(Brightness brightness) {
  final isDark = brightness == Brightness.dark;
  final surface = isDark ? const Color(0xFF0A0A0A) : Colors.white;
  final onSurface = isDark ? const Color(0xFFF9F9F9) : const Color(0xFF0A0A0A);
  final muted = isDark ? const Color(0xFF1A1A1A) : const Color(0xFFF5F5F5);
  const mutedForeground = Color(0xFF737373); // neutral-500
  final border = isDark ? const Color(0xFF1F1F1F) : const Color(0xFFE5E5E5);

  final scheme = ColorScheme(
    brightness: brightness,
    primary: AppTokens.primary,
    onPrimary: Colors.white,
    secondary: onSurface,
    onSecondary: Colors.white,
    error: AppTokens.destructive,
    onError: Colors.white,
    surface: surface,
    onSurface: onSurface,
    surfaceContainerHighest: muted,
    outline: border,
    outlineVariant: border,
  );

  final shape = RoundedRectangleBorder(
    borderRadius: BorderRadius.circular(AppTokens.radius),
  );

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    brightness: brightness,
    scaffoldBackgroundColor: isDark ? const Color(0xFF0A0A0A) : surface,
    fontFamily: 'Roboto', // platform fallback; Geist requires asset add later
    splashFactory: NoSplash.splashFactory,
    visualDensity: VisualDensity.compact,
    appBarTheme: AppBarTheme(
      backgroundColor: surface,
      foregroundColor: onSurface,
      elevation: 0,
      scrolledUnderElevation: 0,
      surfaceTintColor: Colors.transparent,
      centerTitle: false,
      titleTextStyle: TextStyle(
        color: onSurface,
        fontSize: 16,
        fontWeight: FontWeight.w500,
      ),
    ),
    cardTheme: CardThemeData(
      color: surface,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        side: BorderSide(color: border, width: 1),
      ),
      margin: EdgeInsets.zero,
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppTokens.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: shape,
        textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
        minimumSize: const Size(0, 40),
        padding: const EdgeInsets.symmetric(horizontal: 16),
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: onSurface,
        foregroundColor: surface,
        shape: shape,
        minimumSize: const Size(0, 40),
        padding: const EdgeInsets.symmetric(horizontal: 16),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: onSurface,
        shape: shape,
        side: BorderSide(color: border, width: 1),
        minimumSize: const Size(0, 40),
        padding: const EdgeInsets.symmetric(horizontal: 16),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: onSurface,
        shape: shape,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: false,
      isDense: true,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        borderSide: BorderSide(color: border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        borderSide: BorderSide(color: border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        borderSide: const BorderSide(color: AppTokens.primary, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        borderSide: const BorderSide(color: AppTokens.destructive),
      ),
      hintStyle: const TextStyle(color: mutedForeground, fontSize: 14),
    ),
    dividerTheme: DividerThemeData(color: border, thickness: 1, space: 1),
    snackBarTheme: SnackBarThemeData(
      backgroundColor: onSurface,
      contentTextStyle: TextStyle(color: surface),
      shape: shape,
      behavior: SnackBarBehavior.floating,
    ),
    textTheme: TextTheme(
      bodyLarge: TextStyle(color: onSurface, fontSize: 14),
      bodyMedium: TextStyle(color: onSurface, fontSize: 14),
      bodySmall: TextStyle(color: mutedForeground, fontSize: 12),
      titleSmall: TextStyle(color: onSurface, fontSize: 13, fontWeight: FontWeight.w500),
      titleMedium: TextStyle(color: onSurface, fontSize: 16, fontWeight: FontWeight.w500),
      titleLarge: TextStyle(color: onSurface, fontSize: 20, fontWeight: FontWeight.w500),
      labelMedium: TextStyle(color: mutedForeground, fontSize: 12, letterSpacing: 0.5),
    ),
  );
}
