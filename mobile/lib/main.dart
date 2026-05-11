import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'routing/app_router.dart';
import 'shared/preferences.dart';
import 'shared/ui_tokens.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const ProviderScope(child: EpassportApp()));
}

class EpassportApp extends ConsumerWidget {
  const EpassportApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    final prefs = ref.watch(appPreferencesProvider);

    final platformBrightness =
        WidgetsBinding.instance.platformDispatcher.platformBrightness;
    final effective = switch (prefs.themeMode) {
      ThemeMode.dark => Brightness.dark,
      ThemeMode.light => Brightness.light,
      ThemeMode.system => platformBrightness,
    };
    // Must run before MaterialApp builds — descendants read static
    // UiTokens.X getters during the same frame.
    UiTokens.applyBrightness(effective);

    return MaterialApp.router(
      title: 'ePassport',
      theme: buildAppTheme(),
      darkTheme: buildDarkTheme(),
      themeMode: prefs.themeMode,
      locale: prefs.locale,
      supportedLocales: const [Locale('en'), Locale('ar')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      debugShowCheckedModeBanner: false,
      routerConfig: router,
    );
  }
}
