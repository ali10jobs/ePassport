import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';

import '../../api/api_client.dart';
import '../../api/api_exception.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';

/// Hazard report — matches MobileScreens/stitch/hazard_report_full_width_slider.
///
/// Two presentations of the same form:
///   - Anonymous flow (from launch screen): standalone scaffold, no auth shell.
///   - Authenticated flow (Hazards tab): wrapped in AppShell with bottom nav.
///
/// In v1.0 only the anonymous backend endpoint is wired
/// (POST /api/v1/hazard-reports/anonymous). The form is identical in both
/// modes.
class AnonymousHazardScreen extends ConsumerStatefulWidget {
  const AnonymousHazardScreen({super.key, this.inAppShell = false});
  final bool inAppShell;

  @override
  ConsumerState<AnonymousHazardScreen> createState() =>
      _AnonymousHazardScreenState();
}

const _categories = <(String, String, IconData)>[
  ('fall', 'Fall', Icons.height),
  ('fire', 'Fire', Icons.local_fire_department_outlined),
  ('electrical', 'Electrical', Icons.bolt),
  ('toxic', 'Toxic', Icons.coronavirus_outlined),
  ('impact', 'Impact', Icons.directions_car_outlined),
  ('other', 'Other', Icons.more_horiz),
];

class _AnonymousHazardScreenState extends ConsumerState<AnonymousHazardScreen> {
  final _descCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  String _category = 'fall';
  double _severity = 0.85;
  bool _gpsOn = true;
  final List<Uint8List> _photos = [];
  static const int _maxPhotos = 5;
  bool _stripping = false;
  Position? _position;
  bool _resolvingGps = false;
  String? _gpsError;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _resolveGps());
  }

  Future<void> _resolveGps() async {
    if (!_gpsOn) return;
    setState(() {
      _resolvingGps = true;
      _gpsError = null;
    });
    try {
      if (!await Geolocator.isLocationServiceEnabled()) {
        if (!mounted) return;
        setState(() {
          _gpsError = ref.read(stringsProvider).gpsServicesOff;
          _resolvingGps = false;
        });
        return;
      }
      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied ||
          perm == LocationPermission.deniedForever) {
        if (!mounted) return;
        setState(() {
          _gpsError = ref.read(stringsProvider).gpsPermissionDenied;
          _resolvingGps = false;
        });
        return;
      }
      final last = await Geolocator.getLastKnownPosition();
      if (last != null && mounted) {
        setState(() {
          _position = last;
          _resolvingGps = false;
        });
      }
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      ).timeout(const Duration(seconds: 10));
      if (!mounted) return;
      setState(() {
        _position = pos;
        _resolvingGps = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _gpsError = ref.read(stringsProvider).couldNotReachServer;
        _resolvingGps = false;
      });
    }
  }
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _descCtrl.dispose();
    _locationCtrl.dispose();
    super.dispose();
  }

  String _severityApiValue() {
    if (_severity < 0.25) return 'low';
    if (_severity < 0.5) return 'medium';
    if (_severity < 0.75) return 'high';
    return 'critical';
  }

  Future<void> _pickFromCamera() async {
    if (_photos.length >= _maxPhotos) return;
    final picker = ImagePicker();
    final file = await picker.pickImage(
        source: ImageSource.camera, maxWidth: 1920, imageQuality: 85);
    if (file == null) return;
    await _processAndAppend([await file.readAsBytes()]);
  }

  Future<void> _pickFromGallery() async {
    if (_photos.length >= _maxPhotos) return;
    final picker = ImagePicker();
    final remaining = _maxPhotos - _photos.length;
    final files = await picker.pickMultiImage(
      maxWidth: 1920,
      imageQuality: 85,
      limit: remaining,
    );
    if (files.isEmpty) return;
    final selected = files.take(remaining).toList();
    final raws = <Uint8List>[];
    for (final f in selected) {
      raws.add(await f.readAsBytes());
    }
    await _processAndAppend(raws);
  }

  Future<void> _processAndAppend(List<Uint8List> raws) async {
    setState(() => _stripping = true);
    try {
      final processed = <Uint8List>[];
      for (final raw in raws) {
        final decoded = img.decodeImage(raw);
        if (decoded == null) throw Exception('decode failed');
        processed.add(Uint8List.fromList(img.encodeJpg(decoded, quality: 85)));
      }
      setState(() {
        _photos.addAll(processed);
        if (_photos.length > _maxPhotos) {
          _photos.removeRange(_maxPhotos, _photos.length);
        }
        _stripping = false;
      });
    } catch (_) {
      setState(() {
        _error = ref.read(stringsProvider).couldNotProcessPhoto;
        _stripping = false;
      });
    }
  }

  void _removePhotoAt(int i) {
    setState(() => _photos.removeAt(i));
  }

  Future<void> _onSubmit() async {
    final s = ref.read(stringsProvider);
    final desc = _descCtrl.text.trim();
    if (desc.length < 5) {
      setState(() => _error = s.hazardDescriptionRequired);
      return;
    }
    if (_photos.isEmpty) {
      setState(() => _error = s.addPhotoOfHazard);
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
    });
    try {
      final res = await ref.read(apiClientProvider).submitAnonymousHazard(
            description: desc,
            descriptionLang: s.isAr ? 'ar' : 'en',
            severity: _severityApiValue(),
            category: _category,
            photos: _photos,
            latitude: _gpsOn ? _position?.latitude : null,
            longitude: _gpsOn ? _position?.longitude : null,
          );
      if (!mounted) return;
      final route = widget.inAppShell
          ? '/hazards/submitted/${res.anonymousReportId}'
          : '/hazard/submitted/${res.anonymousReportId}';
      context.pushReplacement(route);
    } on ApiException catch (e) {
      setState(() {
        _error = e.message;
        _submitting = false;
      });
    } catch (_) {
      setState(() {
        _error = ref.read(stringsProvider).couldNotSubmitNetwork;
        _submitting = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final body = _buildBody(context);
    if (widget.inAppShell) {
      return AppShell(
        tab: AppTab.hazards,
        bodyPadding: EdgeInsets.zero,
        onBack: () =>
            context.canPop() ? context.pop() : context.go('/hazards'),
        child: body,
      );
    }
    return Scaffold(
      backgroundColor: UiTokens.bg,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _AnonymousHeader(onBack: () => context.canPop() ? context.pop() : context.go('/launch')),
            Expanded(child: body),
          ],
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      children: [
        _SectionTitle(s.hazardCategory),
        const SizedBox(height: 10),
        _CategoryGrid(
          value: _category,
          onChanged: (v) => setState(() => _category = v),
        ),
        const SizedBox(height: 20),
        _SectionTitle(s.hazardDescription),
        const SizedBox(height: 10),
        _DescriptionField(
          controller: _descCtrl,
          hint: s.hazardDescriptionHint,
        ),
        const SizedBox(height: 20),
        _SectionTitle(s.evidence),
        const SizedBox(height: 10),
        _Evidence(
          photos: _photos,
          maxPhotos: _maxPhotos,
          loading: _stripping,
          onCamera: _pickFromCamera,
          onGallery: _pickFromGallery,
          onRemove: _removePhotoAt,
        ),
        const SizedBox(height: 20),
        _SectionTitle(s.severity),
        const SizedBox(height: 8),
        _SeveritySlider(
          value: _severity,
          onChanged: (v) => setState(() => _severity = v),
        ),
        const SizedBox(height: 10),
        _SeverityCallout(label: s.severityLevelLabel(_severity), severity: _severity),
        const SizedBox(height: 20),
        _SectionTitle(s.location),
        const SizedBox(height: 10),
        _MiniLabel(s.location),
        const SizedBox(height: 6),
        _LocationField(controller: _locationCtrl),
        const SizedBox(height: 10),
        _GpsRow(
          on: _gpsOn,
          position: _position,
          resolving: _resolvingGps,
          error: _gpsError,
          onChanged: (v) {
            setState(() => _gpsOn = v);
            if (v) _resolveGps();
          },
        ),
        const SizedBox(height: 18),
        const _PrivacyNotice(),
        if (_error != null) ...[
          const SizedBox(height: 12),
          _ErrorBanner(message: _error!),
        ],
        const SizedBox(height: 16),
        _PrimaryPill(
          label: _submitting ? '…' : s.submitReport,
          icon: Icons.arrow_forward,
          onTap: _submitting ? null : _onSubmit,
        ),
        const SizedBox(height: 10),
        Center(
          child: GestureDetector(
            onTap: _submitting ? null : () {},
            child: Text(
              s.saveAsDraft,
              style: TextStyle(
                color: UiTokens.muted,
                fontSize: 13,
                fontWeight: FontWeight.w600,
                decoration: TextDecoration.underline,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _AnonymousHeader extends ConsumerWidget {
  const _AnonymousHeader({required this.onBack});
  final VoidCallback onBack;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
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
          ),
          const SizedBox(width: 4),
          Text(
            'ePassport',
            style: TextStyle(
              color: UiTokens.ink,
              fontSize: 20,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.3,
            ),
          ),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: UiTokens.surfaceMuted,
              borderRadius: BorderRadius.circular(100),
            ),
            child: Text(
              s.anonymousBadge,
              style: TextStyle(
                color: UiTokens.mutedStrong,
                fontSize: 10,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);
  final String text;
  @override
  Widget build(BuildContext context) => Text(
        text,
        style: TextStyle(
          color: UiTokens.ink,
          fontSize: 18,
          fontWeight: FontWeight.w800,
        ),
      );
}

class _MiniLabel extends StatelessWidget {
  const _MiniLabel(this.text);
  final String text;
  @override
  Widget build(BuildContext context) => Text(
        text,
        style: TextStyle(color: UiTokens.muted, fontSize: 13),
      );
}

class _CategoryGrid extends ConsumerWidget {
  const _CategoryGrid({required this.value, required this.onChanged});
  final String value;
  final ValueChanged<String> onChanged;

  String _labelFor(String key, AppStrings s) => switch (key) {
        'fall' => s.hazFall,
        'fire' => s.hazFire,
        'electrical' => s.hazElectrical,
        'toxic' => s.hazToxic,
        'impact' => s.hazImpact,
        _ => s.hazOther,
      };

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return GridView.count(
      crossAxisCount: 3,
      mainAxisSpacing: 10,
      crossAxisSpacing: 10,
      childAspectRatio: 1.0,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      children: _categories.map((c) {
        final selected = c.$1 == value;
        return GestureDetector(
          onTap: () => onChanged(c.$1),
          child: Container(
            decoration: BoxDecoration(
              color: UiTokens.surface,
              border: Border.all(
                color: selected ? UiTokens.ink : UiTokens.border,
                width: selected ? 2 : 1,
              ),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(c.$3, color: UiTokens.ink, size: 28),
                const SizedBox(height: 8),
                Text(
                  _labelFor(c.$1, s),
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }
}

class _Evidence extends ConsumerWidget {
  const _Evidence({
    required this.photos,
    required this.maxPhotos,
    required this.loading,
    required this.onCamera,
    required this.onGallery,
    required this.onRemove,
  });
  final List<Uint8List> photos;
  final int maxPhotos;
  final bool loading;
  final VoidCallback onCamera;
  final VoidCallback onGallery;
  final ValueChanged<int> onRemove;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final atLimit = photos.length >= maxPhotos;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (photos.isEmpty)
          AspectRatio(
            aspectRatio: 16 / 9,
            child: Container(
              decoration: BoxDecoration(
                color: UiTokens.surfaceMuted,
                border: Border.all(color: UiTokens.border),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Center(
                child: loading
                    ? const CircularProgressIndicator()
                    : Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.photo_camera_outlined,
                              color: UiTokens.muted, size: 28),
                          const SizedBox(height: 6),
                          Text(
                            s.tapToAddPhoto,
                            style: TextStyle(
                              color: UiTokens.muted,
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          )
        else
          SizedBox(
            height: 96,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: photos.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, i) => _PhotoThumb(
                bytes: photos[i],
                onRemove: () => onRemove(i),
              ),
            ),
          ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: (loading || atLimit) ? null : onCamera,
                icon: const Icon(Icons.camera_alt_outlined, size: 16),
                label: Text(s.camera),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: (loading || atLimit) ? null : onGallery,
                icon: const Icon(Icons.photo_library_outlined, size: 16),
                label: Text(s.gallery),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        Text(
          '${photos.length} / $maxPhotos',
          style: TextStyle(color: UiTokens.muted, fontSize: 11),
        ),
      ],
    );
  }
}

class _PhotoThumb extends StatelessWidget {
  const _PhotoThumb({required this.bytes, required this.onRemove});
  final Uint8List bytes;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: Image.memory(bytes, width: 96, height: 96, fit: BoxFit.cover),
        ),
        Positioned(
          top: 2,
          right: 2,
          child: GestureDetector(
            onTap: onRemove,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.6),
                shape: BoxShape.circle,
              ),
              padding: const EdgeInsets.all(4),
              child: const Icon(Icons.close, size: 14, color: Colors.white),
            ),
          ),
        ),
      ],
    );
  }
}

class _SeveritySlider extends ConsumerWidget {
  const _SeveritySlider({required this.value, required this.onChanged});
  final double value;
  final ValueChanged<double> onChanged;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return SliderTheme(
      data: SliderTheme.of(context).copyWith(
        activeTrackColor: UiTokens.inkSolid,
        inactiveTrackColor: UiTokens.border,
        thumbColor: UiTokens.inkSolid,
        overlayColor: UiTokens.inkSolid.withValues(alpha: 0.1),
        trackHeight: 4,
        thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 10),
      ),
      child: Column(
        children: [
          Slider(value: value, onChanged: onChanged),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(s.severityLow, style: TextStyle(color: UiTokens.muted, fontSize: 12)),
                Text(
                  s.severityCritical,
                  style: TextStyle(
                    color: UiTokens.destructive,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
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

class _SeverityCallout extends ConsumerWidget {
  const _SeverityCallout({required this.label, required this.severity});
  final String label;
  final double severity;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final isCritical = severity >= 0.75;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isCritical ? UiTokens.destructiveSoft : UiTokens.surfaceMuted,
        border: Border.all(
          color: isCritical
              ? UiTokens.destructive.withValues(alpha: 0.35)
              : UiTokens.border,
        ),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        children: [
          Icon(
            Icons.warning_amber_rounded,
            color: isCritical ? UiTokens.destructive : UiTokens.muted,
            size: 18,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    color: isCritical ? UiTokens.destructive : UiTokens.ink,
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  s.severityCalloutDescription,
                  style: TextStyle(color: UiTokens.mutedStrong, fontSize: 12, height: 1.3),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _LocationField extends StatelessWidget {
  const _LocationField({required this.controller});
  final TextEditingController controller;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      style: TextStyle(color: UiTokens.ink, fontSize: 14),
      decoration: InputDecoration(
        filled: true,
        fillColor: UiTokens.surface,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: UiTokens.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: UiTokens.ink, width: 1.5),
        ),
      ),
    );
  }
}

class _DescriptionField extends StatelessWidget {
  const _DescriptionField({required this.controller, required this.hint});
  final TextEditingController controller;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      style: TextStyle(color: UiTokens.ink, fontSize: 14),
      minLines: 3,
      maxLines: 6,
      maxLength: 2000,
      textInputAction: TextInputAction.newline,
      decoration: InputDecoration(
        filled: true,
        fillColor: UiTokens.surface,
        hintText: hint,
        hintStyle: TextStyle(color: UiTokens.muted, fontSize: 13),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: UiTokens.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: UiTokens.ink, width: 1.5),
        ),
      ),
    );
  }
}

class _GpsRow extends ConsumerWidget {
  const _GpsRow({
    required this.on,
    required this.onChanged,
    required this.position,
    required this.resolving,
    required this.error,
  });
  final bool on;
  final ValueChanged<bool> onChanged;
  final Position? position;
  final bool resolving;
  final String? error;

  String _subtitle(AppStrings s) {
    if (!on) return '—';
    if (resolving) return s.loadingDashboard;
    if (error != null) return error!;
    final p = position;
    if (p == null) return '—';
    return '${p.latitude.toStringAsFixed(6)}, ${p.longitude.toStringAsFixed(6)}';
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Container(
      padding: const EdgeInsetsDirectional.fromSTEB(14, 10, 8, 10),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(Icons.location_on_outlined,
              color: UiTokens.ink, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  s.attachGps,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Text(
                  _subtitle(s),
                  style: TextStyle(
                    color: error != null ? UiTokens.destructive : UiTokens.muted,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: on,
            onChanged: onChanged,
            activeThumbColor: Colors.white,
            activeTrackColor: UiTokens.inkSolid,
            inactiveThumbColor: UiTokens.muted,
            inactiveTrackColor: UiTokens.surfaceMuted,
            trackOutlineColor: WidgetStateProperty.resolveWith(
              (states) => states.contains(WidgetState.selected)
                  ? UiTokens.inkSolid
                  : UiTokens.border,
            ),
          ),
        ],
      ),
    );
  }
}

class _PrivacyNotice extends ConsumerWidget {
  const _PrivacyNotice();
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: UiTokens.surfaceMuted,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        children: [
          Icon(Icons.lock_outline, color: UiTokens.muted, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              s.privacyNotice,
              style: TextStyle(color: UiTokens.mutedStrong, fontSize: 12, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}

class _PrimaryPill extends StatelessWidget {
  const _PrimaryPill({
    required this.label,
    required this.icon,
    required this.onTap,
  });
  final String label;
  final IconData icon;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 52,
      child: Material(
        color: UiTokens.ink,
        shape: const StadiumBorder(),
        child: InkWell(
          customBorder: const StadiumBorder(),
          onTap: onTap,
          child: Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    color: UiTokens.inkInverse,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(width: 8),
                Icon(icon, size: 18, color: UiTokens.inkInverse),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: UiTokens.destructiveSoft,
        border: Border.all(color: UiTokens.destructive.withValues(alpha: 0.3)),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        message,
        style: TextStyle(color: UiTokens.destructive, fontSize: 13),
      ),
    );
  }
}
