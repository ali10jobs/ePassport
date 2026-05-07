import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';

import '../../theme/app_theme.dart';

class AnonymousHazardSubmittedScreen extends StatelessWidget {
  final String anonymousReportId;
  const AnonymousHazardSubmittedScreen({
    super.key,
    required this.anonymousReportId,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Hazard submitted')),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                width: 36,
                height: 36,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: AppTokens.success,
                  borderRadius: BorderRadius.circular(4),
                ),
                child: const Icon(Icons.check, color: Colors.white, size: 22),
              ),
              const SizedBox(height: 16),
              Text('Thanks — your report has been logged.',
                  style: theme.textTheme.titleLarge),
              const SizedBox(height: 6),
              Text(
                'Use the code below to check status later. Updates from the '
                'safety team will appear on the public status page.',
                style: theme.textTheme.bodySmall,
              ),
              const SizedBox(height: 20),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text('YOUR REPORT CODE',
                          style: TextStyle(
                            fontSize: 11,
                            letterSpacing: 0.6,
                            color: Color(0xFF737373),
                          )),
                      const SizedBox(height: 6),
                      SelectableText(
                        anonymousReportId,
                        style: const TextStyle(
                          fontFamily: 'monospace',
                          fontSize: 15,
                        ),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton.icon(
                        onPressed: () async {
                          await Clipboard.setData(
                              ClipboardData(text: anonymousReportId));
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Copied to clipboard')),
                            );
                          }
                        },
                        icon: const Icon(Icons.copy, size: 16),
                        label: const Text('Copy code'),
                      ),
                    ],
                  ),
                ),
              ),
              const Spacer(),
              FilledButton(
                onPressed: () => context.go('/login'),
                child: const Text('Done'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
