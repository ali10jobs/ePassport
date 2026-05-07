import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../api/api_client.dart';
import '../../api/api_exception.dart';
import '../../api/models.dart';
import '../../routing/auth_state.dart';
import '../../theme/app_theme.dart';
import 'scan_result_overlay.dart';

/// Gate scan screen.
///   - QR camera preview (mobile_scanner) or manual employee_id entry
///   - On decode → POST /scans/verify → full-bleed result overlay
///   - Single de-duplication ref so zxing-style double-fire is harmless
class ScanScreen extends ConsumerStatefulWidget {
  const ScanScreen({super.key});
  @override
  ConsumerState<ScanScreen> createState() => _ScanScreenState();
}

enum _Mode { qr, manual }

class _ScanScreenState extends ConsumerState<ScanScreen> {
  final MobileScannerController _scannerCtrl = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );
  final TextEditingController _manualCtrl = TextEditingController();

  _Mode _mode = _Mode.qr;
  bool _verifying = false;
  String? _lastToken;
  ScanResult? _result;

  @override
  void dispose() {
    _scannerCtrl.dispose();
    _manualCtrl.dispose();
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
      setState(() {
        _result = result;
        _verifying = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      _showError(e.message);
      setState(() {
        _verifying = false;
        _lastToken = null;
      });
    } catch (_) {
      if (!mounted) return;
      _showError('Could not reach the server.');
      setState(() {
        _verifying = false;
        _lastToken = null;
      });
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  void _dismissResult() {
    setState(() {
      _result = null;
      _lastToken = null;
      _manualCtrl.clear();
    });
  }

  Future<void> _onSignOut() async {
    await ref.read(authControllerProvider.notifier).signOut();
  }

  @override
  Widget build(BuildContext context) {
    final result = _result;
    if (result != null) {
      return ScanResultOverlay(result: result, onDismiss: _dismissResult);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Gate scan'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (v) {
              if (v == 'signout') _onSignOut();
              if (v == 'hazard') context.push('/hazard/new');
            },
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'hazard', child: Text('Submit hazard report')),
              PopupMenuItem(value: 'signout', child: Text('Sign out')),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Mode toggle — segmented look
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: Row(
              children: [
                Expanded(
                  child: _ModeButton(
                    label: 'Scan QR',
                    icon: Icons.qr_code_scanner,
                    active: _mode == _Mode.qr,
                    onTap: () => setState(() => _mode = _Mode.qr),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _ModeButton(
                    label: 'Manual entry',
                    icon: Icons.keyboard,
                    active: _mode == _Mode.manual,
                    onTap: () => setState(() => _mode = _Mode.manual),
                  ),
                ),
              ],
            ),
          ),

          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: _mode == _Mode.qr ? _buildQrPanel() : _buildManualPanel(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQrPanel() {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          AspectRatio(
            aspectRatio: 1,
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
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Text(
              'Aim the camera at a worker or equipment QR code. The result '
              'is verified server-side using cert + medical + induction state.',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildManualPanel() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Employee ID',
                style: TextStyle(fontSize: 12, color: Color(0xFF737373))),
            const SizedBox(height: 4),
            TextField(
              controller: _manualCtrl,
              autofocus: true,
              decoration: const InputDecoration(hintText: 'EMP-001'),
              textInputAction: TextInputAction.go,
              onSubmitted: (v) => _verify(employeeId: v.trim()),
              enabled: !_verifying,
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: _verifying
                  ? null
                  : () => _verify(employeeId: _manualCtrl.text.trim()),
              child: Text(_verifying ? 'Verifying…' : 'Verify'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ModeButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool active;
  final VoidCallback onTap;

  const _ModeButton({
    required this.label,
    required this.icon,
    required this.active,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Material(
      color: active ? scheme.onSurface : scheme.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTokens.radius),
        side: BorderSide(color: scheme.outline),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTokens.radius),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 10),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon,
                  size: 16,
                  color: active ? scheme.surface : scheme.onSurface),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  fontSize: 13,
                  color: active ? scheme.surface : scheme.onSurface,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
