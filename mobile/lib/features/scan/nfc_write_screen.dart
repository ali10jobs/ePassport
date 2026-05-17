import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:nfc_manager/nfc_manager.dart';

import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// NFC Handoff screen.
///
/// Three-step flow driven by the web admin's "Write NFC" button:
///
///   1. Web mints a 5-min signed URL → renders a QR.
///   2. This screen scans the QR, fetches the payload (no auth needed —
///      the URL signature is the credential).
///   3. We open an NFC session and write two NDEF records:
///        - mime `application/json` with the verbatim payload bytes
///        - text `"<name_en> (<employee_id>)"`
///
/// Why we don't use the shared ApiClient for the fetch: the URL is absolute
/// and the route is unauthenticated. A bare Dio keeps the credential model
/// explicit (signature in the URL — nothing else attached).
class NfcWriteScreen extends ConsumerStatefulWidget {
  const NfcWriteScreen({super.key});

  @override
  ConsumerState<NfcWriteScreen> createState() => _NfcWriteScreenState();
}

enum _Step { scanning, fetching, waitingForTag, writing, done, error }

class _NfcWriteScreenState extends ConsumerState<NfcWriteScreen> {
  final MobileScannerController _scannerCtrl = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );

  _Step _step = _Step.scanning;
  String? _scannedUrl;
  Map<String, dynamic>? _payload;
  String? _errorMessage;

  @override
  void dispose() {
    _scannerCtrl.dispose();
    // Best-effort: if we left an NFC session open (user backed out mid-tap),
    // closing it here avoids the platform keeping the radio armed.
    NfcManager.instance.stopSession().catchError((_) {});
    super.dispose();
  }

  Future<void> _onQrDetected(String raw) async {
    if (_step != _Step.scanning) return;
    final uri = Uri.tryParse(raw);
    if (uri == null || !uri.isAbsolute || !uri.path.contains('/nfc-handoff/payload')) {
      // Ignore unrelated QRs (e.g. worker passport QRs) silently — the user
      // is free to keep scanning until they hit the right one.
      return;
    }
    setState(() {
      _step = _Step.fetching;
      _scannedUrl = raw;
    });
    await _scannerCtrl.stop();
    await _fetchAndWrite(raw);
  }

  Future<void> _fetchAndWrite(String url) async {
    final s = ref.read(stringsProvider);
    try {
      final res = await Dio().getUri<Map<String, dynamic>>(Uri.parse(url));
      final payload = (res.data?['data'] as Map?)?.cast<String, dynamic>();
      if (payload == null) {
        setState(() {
          _step = _Step.error;
          _errorMessage = s.nfcHandoffBadPayload;
        });
        return;
      }
      setState(() {
        _payload = payload;
        _step = _Step.waitingForTag;
      });
      await _writeToTag(payload);
    } on DioException catch (e) {
      setState(() {
        _step = _Step.error;
        // 403 = expired / invalid signature; surface that explicitly.
        _errorMessage = e.response?.statusCode == 403
            ? s.nfcHandoffExpired
            : '${s.nfcHandoffFetchFailed}: ${e.message ?? ''}';
      });
    } catch (e) {
      setState(() {
        _step = _Step.error;
        _errorMessage = '${s.nfcHandoffFetchFailed}: $e';
      });
    }
  }

  Future<void> _writeToTag(Map<String, dynamic> payload) async {
    final s = ref.read(stringsProvider);
    final available = await NfcManager.instance.isAvailable();
    if (!available) {
      setState(() {
        _step = _Step.error;
        _errorMessage = s.nfcUnavailable;
      });
      return;
    }

    final jsonBytes = Uint8List.fromList(utf8.encode(jsonEncode(payload)));
    final label =
        '${payload['name_en'] ?? ''} (${payload['employee_id'] ?? ''})';

    await NfcManager.instance.startSession(
      alertMessage: s.nfcHoldNear,
      onDiscovered: (NfcTag tag) async {
        final ndef = Ndef.from(tag);
        if (ndef == null || !ndef.isWritable) {
          await NfcManager.instance.stopSession(
            errorMessage: s.nfcTagNotWritable,
          );
          if (mounted) {
            setState(() {
              _step = _Step.error;
              _errorMessage = s.nfcTagNotWritable;
            });
          }
          return;
        }
        try {
          if (mounted) setState(() => _step = _Step.writing);
          await ndef.write(NdefMessage([
            NdefRecord.createMime('application/json', jsonBytes),
            NdefRecord.createText(label),
          ]));
          await NfcManager.instance.stopSession(alertMessage: s.nfcWriteDone);
          if (mounted) setState(() => _step = _Step.done);
        } catch (e) {
          await NfcManager.instance.stopSession(errorMessage: e.toString());
          if (mounted) {
            setState(() {
              _step = _Step.error;
              _errorMessage = '${s.nfcWriteFailed}: $e';
            });
          }
        }
      },
    );
  }

  Future<void> _reset() async {
    setState(() {
      _step = _Step.scanning;
      _scannedUrl = null;
      _payload = null;
      _errorMessage = null;
    });
    await _scannerCtrl.start();
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return AppShell(
      tab: AppTab.scan,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
        child: switch (_step) {
          _Step.scanning => _ScannerView(
              scannerCtrl: _scannerCtrl,
              onDetect: _onQrDetected,
              hint: s.nfcHandoffScanHint,
            ),
          _Step.fetching => _StatusView(
              icon: Icons.cloud_download_outlined,
              title: s.nfcHandoffFetching,
              spinner: true,
            ),
          _Step.waitingForTag => _StatusView(
              icon: Icons.nfc_outlined,
              title: s.nfcHoldNear,
              subtitle: _payloadSummary(),
              spinner: true,
            ),
          _Step.writing => _StatusView(
              icon: Icons.edit_outlined,
              title: s.nfcWriting,
              subtitle: _payloadSummary(),
              spinner: true,
            ),
          _Step.done => _StatusView(
              icon: Icons.check_circle_outline,
              title: s.nfcWriteDone,
              subtitle: _payloadSummary(),
              actionLabel: s.nfcWriteAnother,
              onAction: _reset,
            ),
          _Step.error => _StatusView(
              icon: Icons.error_outline,
              title: s.nfcWriteFailed,
              subtitle: _errorMessage,
              actionLabel: s.tryAgain,
              onAction: _reset,
            ),
        },
      ),
    );
  }

  String? _payloadSummary() {
    final p = _payload;
    if (p == null) return _scannedUrl;
    final name = p['name_en'] ?? '';
    final id = p['employee_id'] ?? '';
    return '$name · $id';
  }
}

class _ScannerView extends StatelessWidget {
  const _ScannerView({
    required this.scannerCtrl,
    required this.onDetect,
    required this.hint,
  });
  final MobileScannerController scannerCtrl;
  final ValueChanged<String> onDetect;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Stack(
              fit: StackFit.expand,
              children: [
                MobileScanner(
                  controller: scannerCtrl,
                  onDetect: (capture) {
                    final raw = capture.barcodes.first.rawValue;
                    if (raw == null || raw.isEmpty) return;
                    onDetect(raw);
                  },
                ),
                Container(color: Colors.black.withValues(alpha: 0.25)),
                Positioned(
                  top: 24,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Text(
                        hint,
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.0,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _StatusView extends StatelessWidget {
  const _StatusView({
    required this.icon,
    required this.title,
    this.subtitle,
    this.spinner = false,
    this.actionLabel,
    this.onAction,
  });
  final IconData icon;
  final String title;
  final String? subtitle;
  final bool spinner;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 64, color: UiTokens.ink),
          const SizedBox(height: 16),
          Text(
            title,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 18,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Text(
                subtitle!,
                textAlign: TextAlign.center,
                style: TextStyle(color: UiTokens.muted, fontSize: 13, height: 1.4),
              ),
            ),
          ],
          if (spinner) ...[
            const SizedBox(height: 24),
            const CircularProgressIndicator(),
          ],
          if (actionLabel != null && onAction != null) ...[
            const SizedBox(height: 24),
            SizedBox(
              height: 48,
              child: Material(
                color: UiTokens.ink,
                shape: const StadiumBorder(),
                child: InkWell(
                  customBorder: const StadiumBorder(),
                  onTap: onAction,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Center(
                      child: Text(
                        actionLabel!,
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
            ),
          ],
        ],
      ),
    );
  }
}
