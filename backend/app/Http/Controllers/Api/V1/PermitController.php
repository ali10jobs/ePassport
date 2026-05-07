<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AttachPermitEquipmentRequest;
use App\Http\Requests\V1\AttachPermitWorkersRequest;
use App\Http\Requests\V1\StorePermitRequest;
use App\Http\Resources\V1\PermitResource;
use App\Models\Permit;
use App\Services\Permit\PermitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Permits
 *
 * Permit-to-Work create + attach. Lifecycle transitions (submit, approve,
 * reject, close) are handled by PermitLifecycleController in Phase 3.4-3.5.
 */
class PermitController extends Controller
{
    public function __construct(private readonly PermitService $permits) {}

    /**
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $permits = QueryBuilder::for(Permit::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('project_id'),
                AllowedFilter::exact('site_id'),
                AllowedFilter::exact('issuing_organization_id'),
                AllowedFilter::exact('permit_type_id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $like = '%'.$value.'%';
                    $query->where(function ($q) use ($like) {
                        $q->where('permit_number', 'ilike', $like)
                            ->orWhere('scope_en', 'ilike', $like)
                            ->orWhere('scope_ar', 'ilike', $like);
                    });
                }),
            ])
            ->allowedSorts(['created_at', 'submitted_at', 'permit_number', 'status'])
            ->allowedIncludes(['permitType'])
            ->withCount(['workers', 'equipment'])
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->appends($request->query());

        return PermitResource::collection($permits);
    }

    /**
     * @authenticated
     */
    public function show(Permit $permit): PermitResource
    {
        $permit->load(['permitType'])->loadCount(['workers', 'equipment']);

        return new PermitResource($permit);
    }

    /**
     * Create a draft permit.
     *
     * @authenticated
     */
    public function store(StorePermitRequest $request): JsonResponse
    {
        $permit = $this->permits->createDraft($request->validated(), $request->user()->id);

        return (new PermitResource($permit->load('permitType')->loadCount(['workers', 'equipment'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Attach workers to a draft permit. Either by ID list or by helmet/coverall
     * QR tokens (or both). Workers can be attached only while the permit is
     * in draft state.
     *
     * @authenticated
     */
    public function attachWorkers(AttachPermitWorkersRequest $request, Permit $permit): JsonResponse
    {
        $this->ensureDraft($permit);

        $byIds = $request->validated('workers') ?? [];
        $byTokens = $request->validated('tokens') ?? [];

        $result = $this->permits->attachWorkers($permit, $byIds, $byTokens, $request->user()->id);

        return response()->json(['data' => $result]);
    }

    /**
     * Attach equipment to a draft permit. Either by ID list or QR tokens.
     *
     * @authenticated
     */
    public function attachEquipment(AttachPermitEquipmentRequest $request, Permit $permit): JsonResponse
    {
        $this->ensureDraft($permit);

        $byIds = $request->validated('equipment_ids') ?? [];
        $byTokens = $request->validated('tokens') ?? [];

        $result = $this->permits->attachEquipment($permit, $byIds, $byTokens, $request->user()->id);

        return response()->json(['data' => $result]);
    }

    private function ensureDraft(Permit $permit): void
    {
        if ($permit->status !== Permit::STATUS_DRAFT) {
            throw new ApiException(
                errorCode: ErrorCodes::PERMIT_INVALID_TRANSITION,
                message: "Cannot modify a permit in '{$permit->status}' status.",
                status: 409,
                details: ['current_status' => $permit->status],
            );
        }
    }
}
