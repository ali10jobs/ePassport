import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../api/api_client.dart';
import '../../api/models.dart';
import '../../shared/app_shell.dart';
import '../../shared/i18n.dart';
import '../../shared/ui_tokens.dart';
import 'hazards_screen.dart' show hazardCategoryLabel, hazardSeverityLabel, hazardStatusLabel;

final _hazardDetailProvider =
    FutureProvider.autoDispose.family<HazardReport, String>((ref, id) async {
  return ref.read(apiClientProvider).fetchHazardReport(id);
});

class HazardDetailScreen extends ConsumerWidget {
  const HazardDetailScreen({super.key, required this.id});
  final String id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final async = ref.watch(_hazardDetailProvider(id));

    return AppShell(
      tab: AppTab.hazards,
      onBack: () => context.canPop() ? context.pop() : context.go('/hazards'),
      child: async.when(
        loading: () => Center(
          child: Text(
            s.loadingDashboard,
            style: TextStyle(color: UiTokens.muted, fontSize: 13),
          ),
        ),
        error: (_, __) => Center(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Text(
              s.couldNotLoadHazard,
              textAlign: TextAlign.center,
              style: TextStyle(color: UiTokens.muted, fontSize: 14),
            ),
          ),
        ),
        data: (report) => _Body(report: report, s: s),
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.report, required this.s});
  final HazardReport report;
  final AppStrings s;

  Color _severityColor() {
    switch (report.severity) {
      case 'critical':
        return UiTokens.danger;
      case 'high':
        return UiTokens.destructive;
      case 'medium':
        return UiTokens.warning;
      default:
        return UiTokens.muted;
    }
  }

  @override
  Widget build(BuildContext context) {
    final df = DateFormat.yMMMd(s.isAr ? 'ar' : 'en').add_jm();
    final created = report.createdAt;
    final resolved = report.resolvedAt;
    final sevColor = _severityColor();
    final shortId = report.id.length > 8
        ? report.id.substring(0, 8)
        : report.id;
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      children: [
        Text(
          s.hazardDetailTitle,
          style: TextStyle(
            color: UiTokens.muted,
            fontSize: 11,
            fontWeight: FontWeight.w700,
            letterSpacing: 1.2,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          '#H-$shortId',
          style: TextStyle(
            color: UiTokens.ink,
            fontSize: 22,
            fontWeight: FontWeight.w800,
            letterSpacing: -0.3,
          ),
        ),
        const SizedBox(height: 18),
        if (report.photos.isNotEmpty) ...[
          _PhotoGallery(photos: report.photos),
          const SizedBox(height: 16),
        ],
        _HeaderCard(
          category: hazardCategoryLabel(s, report.category),
          severity: hazardSeverityLabel(s, report.severity),
          status: hazardStatusLabel(s, report.status),
          severityColor: sevColor,
        ),
        const SizedBox(height: 16),
        _SectionLabel(s.hazardDescriptionField),
        const SizedBox(height: 8),
        _ValueCard(
          text: (report.description?.trim().isNotEmpty ?? false)
              ? report.description!
              : s.noDescriptionProvided,
        ),
        if (created != null) ...[
          const SizedBox(height: 16),
          _SectionLabel(s.hazardSubmittedAt),
          const SizedBox(height: 8),
          _ValueCard(text: df.format(created.toLocal())),
        ],
        if (resolved != null) ...[
          const SizedBox(height: 16),
          _SectionLabel(s.hazardResolvedAt),
          const SizedBox(height: 8),
          _ValueCard(text: df.format(resolved.toLocal())),
        ],
        if (report.latitude != null && report.longitude != null) ...[
          const SizedBox(height: 16),
          _SectionLabel(s.hazardLocationField),
          const SizedBox(height: 8),
          _ValueCard(
            text:
                '${report.latitude!.toStringAsFixed(6)}, ${report.longitude!.toStringAsFixed(6)}',
          ),
        ],
      ],
    );
  }
}

class _PhotoGallery extends StatefulWidget {
  const _PhotoGallery({required this.photos});
  final List<HazardPhotoLink> photos;

  @override
  State<_PhotoGallery> createState() => _PhotoGalleryState();
}

class _PhotoGalleryState extends State<_PhotoGallery> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final photos = widget.photos;
    final active = photos[_index.clamp(0, photos.length - 1)];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          onTap: () => _openLightbox(context, _index),
          child: AspectRatio(
            aspectRatio: 16 / 10,
            child: Container(
              decoration: BoxDecoration(
                color: UiTokens.surfaceMuted,
                border: Border.all(color: UiTokens.border),
                borderRadius: BorderRadius.circular(10),
              ),
              clipBehavior: Clip.antiAlias,
              child: Image.network(
                active.url,
                fit: BoxFit.cover,
                loadingBuilder: (_, child, progress) =>
                    progress == null ? child : const Center(child: CircularProgressIndicator()),
                errorBuilder: (_, __, ___) => Center(
                  child: Icon(Icons.broken_image_outlined,
                      color: UiTokens.muted, size: 28),
                ),
              ),
            ),
          ),
        ),
        if (photos.length > 1) ...[
          const SizedBox(height: 8),
          SizedBox(
            height: 64,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: photos.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final selected = i == _index;
                return GestureDetector(
                  onTap: () => setState(() => _index = i),
                  child: Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: selected ? UiTokens.ink : UiTokens.border,
                        width: selected ? 2 : 1,
                      ),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Image.network(
                      photos[i].url,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: UiTokens.surfaceMuted,
                        child: Icon(Icons.broken_image_outlined,
                            color: UiTokens.muted, size: 16),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ],
    );
  }

  void _openLightbox(BuildContext context, int startIndex) {
    showDialog<void>(
      context: context,
      barrierColor: Colors.black87,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: GestureDetector(
          onTap: () => Navigator.of(context).pop(),
          child: InteractiveViewer(
            child: Image.network(
              widget.photos[startIndex].url,
              fit: BoxFit.contain,
            ),
          ),
        ),
      ),
    );
  }
}

class _HeaderCard extends StatelessWidget {
  const _HeaderCard({
    required this.category,
    required this.severity,
    required this.status,
    required this.severityColor,
  });
  final String category;
  final String severity;
  final String status;
  final Color severityColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 8,
                height: 36,
                decoration: BoxDecoration(
                  color: severityColor,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  category,
                  style: TextStyle(
                    color: UiTokens.ink,
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: severityColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  severity,
                  style: TextStyle(
                    color: severityColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.3,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: UiTokens.surfaceMuted,
              borderRadius: BorderRadius.circular(999),
              border: Border.all(color: UiTokens.border),
            ),
            child: Text(
              status,
              style: TextStyle(
                color: UiTokens.ink,
                fontSize: 11,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: TextStyle(
        color: UiTokens.muted,
        fontSize: 11,
        fontWeight: FontWeight.w700,
        letterSpacing: 1.2,
      ),
    );
  }
}

class _ValueCard extends StatelessWidget {
  const _ValueCard({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
      decoration: BoxDecoration(
        color: UiTokens.surface,
        border: Border.all(color: UiTokens.border),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        text,
        style: TextStyle(color: UiTokens.ink, fontSize: 14, height: 1.4),
      ),
    );
  }
}
