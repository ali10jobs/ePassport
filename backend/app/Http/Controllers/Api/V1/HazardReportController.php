<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\DomainEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddHazardNoteRequest;
use App\Http\Requests\V1\UpdateHazardStatusRequest;
use App\Models\HazardReport;
use App\Models\WebhookSubscription;
use App\Services\Authorization\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group Hazard Reports — Authenticated
 *
 * Authenticated views: list, single read, status updates, internal/public notes.
 *
 * Authorization scoping is handled at the policy layer (added in Phase 3+).
 * For week-1 the controller is auth-only; per-user/per-org filtering will be
 * layered in via policy + query scope.
 */
class HazardReportController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Request $request, OrganizationContext $orgContext): AnonymousResourceCollection
    {
        $ctx = $orgContext->forRequest($request);
        $accessibleProjectIds = $ctx->accessibleProjectIds();
        $accessibleOrgIds = $ctx->accessibleOrganizationIds();

        $reports = QueryBuilder::for(
            HazardReport::query()->where(function ($q) use ($accessibleProjectIds, $accessibleOrgIds) {
                // Hazard reports are visible if scoped to a project the user
                // can see, OR assigned to an org they belong to. Reports with
                // no project_id and no assignment (rare in real use) are NOT
                // exposed via this endpoint — they're only retrievable by
                // anonymous_report_id at the public status endpoint.
                $q->whereIn('project_id', $accessibleProjectIds)
                    ->orWhereIn('assigned_to_organization_id', $accessibleOrgIds);
            })
        )
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('severity'),
                AllowedFilter::exact('category'),
                AllowedFilter::exact('project_id'),
                AllowedFilter::exact('site_id'),
                AllowedFilter::exact('assigned_to_organization_id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $like = '%'.$value.'%';
                    $query->where('description', 'ilike', $like);
                }),
            ])
            ->allowedSorts(['created_at', 'severity', 'status'])
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->appends($request->query());

        return JsonResource::collection($reports->through(fn (HazardReport $r) => [
            'id' => $r->id,
            'anonymous_report_id' => $r->anonymous_report_id,
            'is_anonymous' => (bool) $r->is_anonymous,
            'category' => $r->category,
            'severity' => $r->severity,
            'status' => $r->status,
            'description' => $r->description,
            'description_lang' => $r->description_lang,
            'project_id' => $r->project_id,
            'site_id' => $r->site_id,
            'assigned_to_organization_id' => $r->assigned_to_organization_id,
            'photo_path' => $r->metadata['photo_path'] ?? null,
            'photo_paths' => self::photoPathsFor($r),
            'created_at' => $r->created_at?->toIso8601String(),
            'resolved_at' => $r->resolved_at?->toIso8601String(),
        ]));
    }

    /**
     * @authenticated
     */
    public function show(HazardReport $hazardReport): JsonResponse
    {
        $hazardReport->load(['notes', 'reporter', 'assignedToUser', 'assignedToOrganization']);

        return response()->json([
            'data' => [
                'id' => $hazardReport->id,
                'anonymous_report_id' => $hazardReport->anonymous_report_id,
                'is_anonymous' => (bool) $hazardReport->is_anonymous,
                'category' => $hazardReport->category,
                'severity' => $hazardReport->severity,
                'status' => $hazardReport->status,
                'description' => $hazardReport->description,
                'description_lang' => $hazardReport->description_lang,
                'latitude' => $hazardReport->latitude,
                'longitude' => $hazardReport->longitude,
                'project_id' => $hazardReport->project_id,
                'site_id' => $hazardReport->site_id,
                'reporter_user_id' => $hazardReport->reporter_user_id,
                'assigned_to_user_id' => $hazardReport->assigned_to_user_id,
                'assigned_to_organization_id' => $hazardReport->assigned_to_organization_id,
                'resolution_summary' => $hazardReport->resolution_summary,
                'resolved_at' => $hazardReport->resolved_at?->toIso8601String(),
                'photo_path' => $hazardReport->metadata['photo_path'] ?? null,
                'photo_paths' => self::photoPathsFor($hazardReport),
                'photos' => self::photoLinksFor($hazardReport),
                'notes' => $hazardReport->notes->map(fn ($n) => [
                    'id' => $n->id,
                    'note_type' => $n->note_type,
                    'author_user_id' => $n->author_user_id,
                    'author_organization_id' => $n->author_organization_id,
                    'body' => $n->body,
                    'body_lang' => $n->body_lang,
                    'created_at' => $n->created_at?->toIso8601String(),
                ]),
                'created_at' => $hazardReport->created_at?->toIso8601String(),
                'updated_at' => $hazardReport->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update status / assignment / resolution of a hazard report.
     *
     * @authenticated
     */
    public function updateStatus(UpdateHazardStatusRequest $request, HazardReport $hazardReport): JsonResponse
    {
        $data = $request->validated();
        $previousStatus = $hazardReport->status;

        if ($data['status'] === HazardReport::STATUS_RESOLVED && empty($hazardReport->resolved_at)) {
            $data['resolved_at'] = now();
        }

        $hazardReport->update($data);

        if ($previousStatus !== $data['status']) {
            $payload = [
                'hazard_report_id' => $hazardReport->id,
                'anonymous_report_id' => $hazardReport->anonymous_report_id,
                'previous_status' => $previousStatus,
                'status' => $hazardReport->status,
                'severity' => $hazardReport->severity,
                'category' => $hazardReport->category,
            ];
            DomainEvent::dispatch(WebhookSubscription::EVENT_HAZARD_STATUS_CHANGED, $payload);
            if ($hazardReport->status === HazardReport::STATUS_RESOLVED) {
                DomainEvent::dispatch(WebhookSubscription::EVENT_HAZARD_RESOLVED, array_merge($payload, [
                    'resolution_summary' => $hazardReport->resolution_summary,
                    'resolved_at' => $hazardReport->resolved_at?->toIso8601String(),
                ]));
            }
        }

        return $this->show($hazardReport->fresh());
    }

    /**
     * Add an internal or public note. Internal notes are visible only via
     * authenticated GET; public notes appear on the anonymous status check.
     *
     * @authenticated
     */
    public function addNote(AddHazardNoteRequest $request, HazardReport $hazardReport): JsonResponse
    {
        $note = $hazardReport->notes()->create([
            'note_type' => $request->validated('note_type'),
            'author_user_id' => $request->user()?->id,
            'author_organization_id' => $request->validated('author_organization_id'),
            'body' => $request->validated('body'),
            'body_lang' => $request->validated('body_lang'),
        ]);

        return response()->json([
            'data' => [
                'id' => $note->id,
                'note_type' => $note->note_type,
                'author_user_id' => $note->author_user_id,
                'body' => $note->body,
                'body_lang' => $note->body_lang,
                'created_at' => $note->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Stream a single hazard photo by index. Reached via a signed URL (no
     * auth middleware) so mobile clients can use Image.network without
     * needing to attach a bearer token.
     */
    public function photo(Request $request, HazardReport $hazardReport, int $index): StreamedResponse
    {
        $paths = self::photoPathsFor($hazardReport);
        if ($index < 0 || $index >= count($paths)) {
            abort(404);
        }
        $disk = Storage::disk(config('filesystems.default'));
        if (! $disk->exists($paths[$index])) {
            abort(404);
        }

        return $disk->response($paths[$index], headers: [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=600',
        ]);
    }

    /**
     * Photo paths for a report, preferring the new `photo_paths` array and
     * falling back to the legacy single `photo_path` for older records.
     *
     * @return list<string>
     */
    private static function photoPathsFor(HazardReport $r): array
    {
        $metadata = $r->metadata ?? [];
        $paths = $metadata['photo_paths'] ?? null;
        if (is_array($paths) && count($paths) > 0) {
            return array_values(array_filter($paths, fn ($p) => is_string($p) && $p !== ''));
        }
        $legacy = $metadata['photo_path'] ?? null;
        if (is_string($legacy) && $legacy !== '') {
            return [$legacy];
        }

        return [];
    }

    /**
     * Signed URLs for each photo so the mobile client can render images
     * without re-attaching the bearer token on each load.
     *
     * @return list<array{index:int,url:string}>
     */
    private static function photoLinksFor(HazardReport $r): array
    {
        $paths = self::photoPathsFor($r);
        $links = [];
        foreach ($paths as $i => $_) {
            $links[] = [
                'index' => $i,
                'url' => URL::temporarySignedRoute(
                    'v1.hazards.photo',
                    now()->addHours(2),
                    ['hazardReport' => $r->id, 'index' => $i],
                ),
            ];
        }

        return $links;
    }
}
