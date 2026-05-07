import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';

import '../../api/api_client.dart';
import '../../api/api_exception.dart';

/// Anonymous hazard report — public, no auth.
///   1. Take a photo (or pick from gallery).
///   2. Strip EXIF (location, device) before upload — privacy contract.
///   3. Pick severity + category, write a description.
///   4. POST /api/v1/hazard-reports/anonymous
///   5. Surface the anonymous_report_id so the reporter can check status
///      later via /hazard-status?id=...
class AnonymousHazardScreen extends ConsumerStatefulWidget {
  const AnonymousHazardScreen({super.key});
  @override
  ConsumerState<AnonymousHazardScreen> createState() =>
      _AnonymousHazardScreenState();
}

const _categories = [
  ('fall', 'Fall'),
  ('electrical', 'Electrical'),
  ('fire', 'Fire'),
  ('working_at_heights', 'Working at heights'),
  ('lifting', 'Lifting'),
  ('housekeeping', 'Housekeeping'),
  ('ppe', 'PPE'),
  ('environmental', 'Environmental'),
  ('other', 'Other'),
];

const _severities = [
  ('low', 'Low'),
  ('medium', 'Medium'),
  ('high', 'High'),
  ('critical', 'Critical'),
];

class _AnonymousHazardScreenState
    extends ConsumerState<AnonymousHazardScreen> {
  final _descCtrl = TextEditingController();
  String _category = 'other';
  String _severity = 'medium';
  Uint8List? _photoBytes;
  bool _stripping = false;
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _descCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto({required ImageSource source}) async {
    final picker = ImagePicker();
    final file = await picker.pickImage(
      source: source,
      maxWidth: 1920,
      imageQuality: 85,
    );
    if (file == null) return;
    setState(() => _stripping = true);
    try {
      final raw = await file.readAsBytes();
      final decoded = img.decodeImage(raw);
      if (decoded == null) throw Exception('Could not decode image');
      // Re-encode without EXIF — image package drops metadata by default.
      final stripped = Uint8List.fromList(img.encodeJpg(decoded, quality: 85));
      setState(() {
        _photoBytes = stripped;
        _stripping = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Could not process photo';
        _stripping = false;
      });
    }
  }

  Future<void> _onSubmit() async {
    if (_photoBytes == null) {
      setState(() => _error = 'A photo is required.');
      return;
    }
    if (_descCtrl.text.trim().isEmpty) {
      setState(() => _error = 'Add a short description.');
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
    });
    try {
      final res = await ref.read(apiClientProvider).submitAnonymousHazard(
            description: _descCtrl.text.trim(),
            descriptionLang: 'en',
            severity: _severity,
            category: _category,
            photoBytes: _photoBytes!,
          );
      if (!mounted) return;
      context.pushReplacement('/hazard/submitted/${res.anonymousReportId}');
    } on ApiException catch (e) {
      setState(() {
        _error = e.message;
        _submitting = false;
      });
    } catch (_) {
      setState(() {
        _error = 'Could not submit. Check your network and try again.';
        _submitting = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Submit hazard')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _PrivacyCallout(),
            const SizedBox(height: 16),

            _Label('Photo'),
            const SizedBox(height: 4),
            _PhotoField(
              bytes: _photoBytes,
              loading: _stripping,
              onCamera: () => _pickPhoto(source: ImageSource.camera),
              onGallery: () => _pickPhoto(source: ImageSource.gallery),
            ),
            const SizedBox(height: 16),

            _Label('Severity'),
            const SizedBox(height: 4),
            DropdownButtonFormField<String>(
              initialValue: _severity,
              isDense: true,
              items: _severities
                  .map((s) =>
                      DropdownMenuItem(value: s.$1, child: Text(s.$2)))
                  .toList(),
              onChanged: (v) => setState(() => _severity = v!),
            ),
            const SizedBox(height: 12),

            _Label('Category'),
            const SizedBox(height: 4),
            DropdownButtonFormField<String>(
              initialValue: _category,
              isDense: true,
              items: _categories
                  .map((c) =>
                      DropdownMenuItem(value: c.$1, child: Text(c.$2)))
                  .toList(),
              onChanged: (v) => setState(() => _category = v!),
            ),
            const SizedBox(height: 12),

            _Label('What did you see?'),
            const SizedBox(height: 4),
            TextField(
              controller: _descCtrl,
              minLines: 3,
              maxLines: 6,
              maxLength: 1500,
              decoration: const InputDecoration(
                hintText:
                    'Describe the hazard, location on site, and any people at risk.',
              ),
            ),

            if (_error != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.error.withValues(alpha: 0.06),
                  border: Border.all(
                      color:
                          Theme.of(context).colorScheme.error.withValues(alpha: 0.3)),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  _error!,
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.error,
                    fontSize: 12,
                  ),
                ),
              ),
            ],

            const SizedBox(height: 16),
            FilledButton(
              onPressed: _submitting ? null : _onSubmit,
              child: Text(_submitting ? 'Submitting…' : 'Submit anonymously'),
            ),
          ],
        ),
      ),
    );
  }
}

class _Label extends StatelessWidget {
  final String text;
  const _Label(this.text);
  @override
  Widget build(BuildContext context) => Text(text,
      style: const TextStyle(
        fontSize: 12,
        color: Color(0xFF737373),
        letterSpacing: 0.4,
      ));
}

class _PrivacyCallout extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF5F5F5),
        borderRadius: BorderRadius.circular(4),
      ),
      child: const Text(
        'No login required. We strip image metadata before upload, and the '
        'safety team only sees your description, severity, and photo. You '
        'will receive a code to check status later.',
        style: TextStyle(fontSize: 12, color: Color(0xFF404040)),
      ),
    );
  }
}

class _PhotoField extends StatelessWidget {
  final Uint8List? bytes;
  final bool loading;
  final VoidCallback onCamera;
  final VoidCallback onGallery;

  const _PhotoField({
    required this.bytes,
    required this.loading,
    required this.onCamera,
    required this.onGallery,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (bytes != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: Image.memory(bytes!, height: 220, fit: BoxFit.cover),
              )
            else
              Container(
                height: 220,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: const Color(0xFFFAFAFA),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: loading
                    ? const CircularProgressIndicator()
                    : const Text(
                        'No photo selected',
                        style: TextStyle(color: Color(0xFF737373), fontSize: 13),
                      ),
              ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: loading ? null : onCamera,
                    icon: const Icon(Icons.camera_alt_outlined, size: 16),
                    label: const Text('Camera'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: loading ? null : onGallery,
                    icon: const Icon(Icons.photo_library_outlined, size: 16),
                    label: const Text('Gallery'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

