<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\DomainEvent;
use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SubmitAnonymousHazardRequest;
use App\Models\HazardReport;
use App\Models\HazardReportNote;
use App\Models\WebhookSubscription;
use App\Services\Authorization\OrganizationContext;
use App\Services\Hazard\HazardPhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @group Hazard Reports — Anonymous (public)
 *
 * Public endpoints for the anonymous reporter flow. No PII captured at the
 * application layer:
 *   - no IP stored
 *   - no User-Agent / device fingerprint stored
 *   - no submitter_id column at the schema level (see migration)
 *
 * The handle returned is anonymous_report_id (random UUID); the submitter
 * uses it to check status without authenticating.
 */
class HazardReportAnonymousController extends Controller
{
    public function __construct(
        private readonly HazardPhotoService $photos,
        private readonly OrganizationContext $orgContext,
    ) {}

    /**
     * Submit an anonymous hazard report. Photo EXIF stripped server-side
     * before storage.
     *
     * @unauthenticated
     */
    public function store(SubmitAnonymousHazardRequest $request): JsonResponse
    {
        // Strip EXIF and re-encode each uploaded photo. We collect all stored
        // paths into `photo_paths`; `photo_path` is kept set to the first one
        // for any reader still on the legacy single-photo field.
        $photoPaths = [];
        $totalBytes = 0;
        foreach ($request->uploadedPhotos() as $upload) {
            $cleanedBytes = $this->photos->stripExifAndReencode($upload);
            if (! $this->photos->verifyNoExif($cleanedBytes)) {
                throw new ApiException(
                    errorCode: ErrorCodes::HAZARD_EXIF_STRIP_FAILED,
                    message: 'Photo EXIF strip failed. The photo could not be safely processed for anonymous submission.',
                    status: 422,
                );
            }
            $filename = sprintf('hazard-photos/%s/%s.jpg', date('Y/m'), Str::uuid());
            Storage::disk(config('filesystems.default'))->put($filename, $cleanedBytes);
            $photoPaths[] = $filename;
            $totalBytes += strlen($cleanedBytes);
        }

        $anonId = (string) Str::uuid();

        // The submitter may attach their identity by passing make_public_identity=true
        // together with a valid Sanctum token. Without the flag (the default) the
        // report stays fully anonymous — no reporter_user_id, no is_anonymous flip.
        $authUser = Auth::guard('sanctum')->user();
        $makePublicIdentity = (bool) $request->validated('make_public_identity', false);
        $attributeToUser = $makePublicIdentity && $authUser !== null;

        // If the submitter is authenticated and didn't pass a project_id,
        // infer one from their accessible projects so the report rolls into
        // the right inbox / dashboard.
        $projectId = $request->validated('project_id');
        if ($projectId === null && $authUser !== null) {
            $request->setUserResolver(fn () => $authUser);
            $accessible = $this->orgContext->forRequest($request)->accessibleProjectIds();
            $projectId = $accessible[0] ?? null;
        }

        $report = HazardReport::create([
            'anonymous_report_id' => $anonId,
            'is_anonymous' => ! $attributeToUser,
            'reporter_user_id' => $attributeToUser ? $authUser->id : null,
            'project_id' => $projectId,
            'site_id' => $request->validated('site_id'),
            'category' => $request->validated('category'),
            'severity' => $request->validated('severity'),
            'description' => $request->validated('description'),
            'description_lang' => $request->validated('description_lang'),
            'latitude' => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'status' => HazardReport::STATUS_SUBMITTED,
            'metadata' => [
                'photo_path' => $photoPaths[0] ?? null,
                'photo_paths' => $photoPaths,
                'photo_bytes' => $totalBytes,
            ],
        ]);

        DomainEvent::dispatch(WebhookSubscription::EVENT_HAZARD_SUBMITTED, [
            'hazard_report_id' => $report->id,
            'anonymous_report_id' => $report->anonymous_report_id,
            'category' => $report->category,
            'severity' => $report->severity,
            'project_id' => $report->project_id,
            'site_id' => $report->site_id,
            'submitted_at' => $report->created_at->toIso8601String(),
        ]);

        return response()->json([
            'data' => [
                'anonymous_report_id' => $report->anonymous_report_id,
                'status' => $report->status,
                'submitted_at' => $report->created_at->toIso8601String(),
                'is_anonymous' => $report->is_anonymous,
                'reporter_name' => $attributeToUser ? $authUser->name : null,
                'check_status_url' => sprintf('/api/v1/hazard-reports/anonymous/%s', $report->anonymous_report_id),
            ],
        ], 201);
    }

    /**
     * Public status check. Returns ONLY non-PII fields and PUBLIC notes.
     * Internal contractor notes are never exposed here.
     *
     * @unauthenticated
     */
    public function status(string $anonymousReportId): JsonResponse
    {
        $report = HazardReport::where('anonymous_report_id', $anonymousReportId)->first();

        if ($report === null) {
            throw new ApiException(
                errorCode: ErrorCodes::RESOURCE_NOT_FOUND,
                message: 'No hazard report found for the supplied id.',
                status: 404,
            );
        }

        $publicNotes = $report->notes()
            ->where('note_type', HazardReportNote::TYPE_PUBLIC)
            ->orderBy('created_at')
            ->get(['id', 'body', 'body_lang', 'created_at']);

        return response()->json([
            'data' => [
                'anonymous_report_id' => $report->anonymous_report_id,
                'status' => $report->status,
                'category' => $report->category,
                'severity' => $report->severity,
                'submitted_at' => $report->created_at->toIso8601String(),
                'resolved_at' => $report->resolved_at?->toIso8601String(),
                'resolution_summary' => $report->resolution_summary,
                'public_updates' => $publicNotes->map(fn ($n) => [
                    'body' => $n->body,
                    'body_lang' => $n->body_lang,
                    'posted_at' => $n->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }
}
