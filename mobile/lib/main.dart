import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'routing/app_router.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const ProviderScope(child: EpassportApp()));
}

class EpassportApp extends ConsumerWidget {
  const EpassportApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    return MaterialApp.router(
      title: 'ePassport',
      theme: buildAppTheme(),
      debugShowCheckedModeBanner: false,
      routerConfig: router,
    );
  }
}
