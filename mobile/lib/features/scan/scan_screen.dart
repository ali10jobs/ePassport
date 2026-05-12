import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../api/api_client.dart';
import '../../api/api_exception.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';
import 'scan_result_screen.dart';

/// Gate scan — matches MobileScreens/stitch/gate_scan_header_refined.
/// Camera viewport with corner brackets + "ENTER ID MANUALLY" fallback.
/// On decode → POST /scans/verify → ScanResultScreen.
class ScanScreen extends ConsumerStatefulWidget {
  const ScanScreen({super.key});
  @override
  ConsumerState<ScanScreen> createState() => _ScanScreenState();
}

class _ScanScreenState extends ConsumerState<ScanScreen> {
  final MobileScannerController _scannerCtrl = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );

  bool _verifying = false;
  String? _lastToken;
  bool _flashOn = false;

  @override
  void dispose() {
    _scannerCtrl.dispose();
    super.dispose();
  }

  Future<void> _verify({String? token, String? employeeId}) async {
    if (_verifying) return;
    if (token != null && token == _lastToken) return;
    _lastToken = token;
    setState(() => _verifying = true);
    try {
      final result = await ref
          .read(apiClientProvider)
          .verifyScan(token: token, employeeId: employeeId);
      if (!mounted) return;
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => ScanResultScreen(
            result: result,
            onDone: () => Navigator.of(context).pop(),
          ),
        ),
      );
    } on ApiException catch (e) {
      if (mounted) _showError(e.message);
    } catch (_) {
      if (mounted) {
        _showError(ref.read(stringsProvider).couldNotReachServer);
      }
    } finally {
      if (mounted) {
        setState(() {
          _verifying = false;
          _lastToken = null;
        });
      }
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _openManualEntry() async {
    final id = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: UiTokens.surface,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => const _ManualEntrySheet(),
    );
    if (id != null && id.isNotEmpty) {
      _verify(employeeId: id);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AppShell(
      tab: AppTab.scan,
      headerAction: _FlashToggle(
        on: _flashOn,
        onTap: () {
          _scannerCtrl.toggleTorch();
          setState(() => _flashOn = !_flashOn);
        },
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
        child: Column(
          children: [
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    MobileScanner(
                      controller: _scannerCtrl,
                      onDetect: (capture) {
                        final raw = capture.barcodes.first.rawValue;
                        if (raw == null || raw.isEmpty) return;
                        _verify(token: raw);
                      },
                      errorBuilder: (context, error, _) =>
                          _CameraUnavailable(error: error),
                    ),
                    Container(color: Colors.black.withValues(alpha: 0.25)),
                    const Center(
                      child: SizedBox.square(
                        dimension: 240,
                        child: _CornerBrackets(),
                      ),
                    ),
                    Positioned(
                      top: 24,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: Text(
                          ref.watch(stringsProvider).alignQr,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 1.2,
                          ),
                        ),
                      ),
                    ),
                    if (_verifying)
                      Container(
                        color: Colors.black54,
                        alignment: Alignment.center,
                        child: const CircularProgressIndicator(color: Colors.white),
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            _ManualEntryButton(onTap: _openManualEntry),
          ],
        ),
      ),
    );
  }
}

class _FlashToggle extends StatelessWidget {
  const _FlashToggle({required this.on, required this.onTap});
  final bool on;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: on ? UiTokens.ink : UiTokens.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: BorderSide(color: UiTokens.border),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: SizedBox(
          width: 40,
          height: 36,
          child: Icon(
            on ? Icons.flash_on : Icons.flash_off,
            size: 18,
            color: on ? Colors.white : UiTokens.ink,
          ),
        ),
      ),
    );
  }
}

class _CornerBrackets extends StatelessWidget {
  const _CornerBrackets();
  @override
  Widget build(BuildContext context) {
    return CustomPaint(painter: _BracketsPainter());
  }
}

class _BracketsPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.square;
    const len = 28.0;
    // Top-left
    canvas.drawLine(const Offset(0, 0), const Offset(len, 0), paint);
    canvas.drawLine(const Offset(0, 0), const Offset(0, len), paint);
    // Top-right
    canvas.drawLine(Offset(size.width - len, 0), Offset(size.width, 0), paint);
    canvas.drawLine(Offset(size.width, 0), Offset(size.width, len), paint);
    // Bottom-left
    canvas.drawLine(Offset(0, size.height - len), Offset(0, size.height), paint);
    canvas.drawLine(Offset(0, size.height), Offset(len, size.height), paint);
    // Bottom-right
    canvas.drawLine(Offset(size.width, size.height - len),
        Offset(size.width, size.height), paint);
    canvas.drawLine(Offset(size.width - len, size.height),
        Offset(size.width, size.height), paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _CameraUnavailable extends ConsumerWidget {
  const _CameraUnavailable({required this.error});
  final MobileScannerException error;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Container(
      color: const Color(0xFF1A1A1A),
      alignment: Alignment.center,
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.no_photography_outlined,
              color: Colors.white54, size: 36),
          const SizedBox(height: 12),
          Text(
            s.cameraUnavailable,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            error.errorDetails?.message ?? s.useManualEntry,
            textAlign: TextAlign.center,
            style: const TextStyle(color: Colors.white54, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

class _ManualEntryButton extends ConsumerWidget {
  const _ManualEntryButton({required this.onTap});
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return SizedBox(
      height: 52,
      child: Material(
        color: UiTokens.surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(100),
          side: BorderSide(color: UiTokens.border),
        ),
        child: InkWell(
          customBorder: const StadiumBorder(),
          onTap: onTap,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.keyboard_outlined, size: 18, color: UiTokens.ink),
              const SizedBox(width: 8),
              Text(
                s.enterIdManually,
                style: TextStyle(
                  color: UiTokens.ink,
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.0,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ManualEntrySheet extends ConsumerStatefulWidget {
  const _ManualEntrySheet();
  @override
  ConsumerState<_ManualEntrySheet> createState() => _ManualEntrySheetState();
}

class _ManualEntrySheetState extends ConsumerState<_ManualEntrySheet> {
  final _ctrl = TextEditingController();

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final viewInsets = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + viewInsets),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            s.manualEntry,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 18,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            s.manualEntryBlurb,
            style: TextStyle(color: UiTokens.muted, fontSize: 13, height: 1.4),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _ctrl,
            autofocus: true,
            textInputAction: TextInputAction.go,
            keyboardType: TextInputType.number,
            maxLength: 10,
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            onSubmitted: (v) => Navigator.of(context).pop(v.trim()),
            style: TextStyle(color: UiTokens.ink, fontSize: 15),
            decoration: InputDecoration(
              counterText: '',
              hintText: '9XXXXXXXXX',
              hintStyle: TextStyle(color: UiTokens.muted),
              filled: true,
              fillColor: UiTokens.inputFill,
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(6),
                borderSide: BorderSide(color: UiTokens.border),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(6),
                borderSide: BorderSide(color: UiTokens.ink, width: 1.5),
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 48,
            child: Material(
              color: UiTokens.ink,
              shape: const StadiumBorder(),
              child: InkWell(
                customBorder: const StadiumBorder(),
                onTap: () => Navigator.of(context).pop(_ctrl.text.trim()),
                child: Center(
                  child: Text(
                    s.verify,
                    style: TextStyle(
                      color: UiTokens.inkInverse,
                      fontSize: 13,
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
    );
  }
}
