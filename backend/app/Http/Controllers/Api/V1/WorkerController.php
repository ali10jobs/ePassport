<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BulkWorkerRequest;
use App\Http\Requests\V1\StoreWorkerRequest;
use App\Http\Requests\V1\UpdateWorkerRequest;
use App\Http\Resources\V1\WorkerResource;
use App\Models\Worker;
use App\Services\QrCode\QrCodeService;
use App\Services\Worker\BulkWorkerService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Workers
 *
 * CRUD plus the e-Passport read model and helmet/coverall QR PNG generation.
 */
class WorkerController extends Controller
{
    public function __construct(
        private readonly WorkerService $workers,
        private readonly QrCodeService $qrCodes,
        private readonly BulkWorkerService $bulk,
    ) {
    }

    /**
     * List workers.
     *
     * Supports filter[employer_organization_id], filter[induction_status],
     * filter[search] (matches name + employee_id + national_id), and
     * filter[expiring_within] (days; matches workers with any cert expiring
     * within N days).
     *
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $workers = QueryBuilder::for(Worker::class)
            ->allowedFilters([
                AllowedFilter::exact('employer_organization_id'),
                AllowedFilter::exact('induction_status'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $like = '%'.$value.'%';
                        $q->where('first_name_en', 'ilike', $like)
                            ->orWhere('last_name_en', 'ilike', $like)
                            ->orWhere('first_name_ar', 'ilike', $like)
                            ->orWhere('last_name_ar', 'ilike', $like)
                            ->orWhere('employee_id', 'ilike', $like)
                            ->orWhere('national_id', 'ilike', $like)
                            ->orWhere('iqama_number', 'ilike', $like);
                    });
                }),
                AllowedFilter::callback('expiring_within', function ($query, $value) {
                    $days = max(0, (int) $value);
                    $query->whereHas('certifications', function ($q) use ($days) {
                        $q->whereNotNull('expiry_date')
                            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
                    });
                }),
                AllowedFilter::callback('cert_status', function ($query, $value) {
                    if ($value === 'expired') {
                        $query->whereHas('certifications', fn ($q) => $q->where('expiry_date', '<', now()->toDateString()));
                    } elseif ($value === 'valid') {
                        $query->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '<', now()->toDateString()));
                    }
                }),
            ])
            ->allowedSorts(['created_at', 'last_name_en', 'employee_id'])
            ->allowedIncludes(['employerOrganization', 'certifications.certificationType', 'latestMedicalRecord'])
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->appends($request->query());

        return WorkerResource::collection($workers);
    }

    /**
     * Get a single worker.
     *
     * @authenticated
     */
    public function show(Worker $worker): WorkerResource
    {
        $worker->load(['employerOrganization']);

        return new WorkerResource($worker);
    }

    /**
     * Create a worker.
     *
     * @authenticated
     */
    public function store(StoreWorkerRequest $request): JsonResponse
    {
        $worker = $this->workers->create($request->validated());

        return (new WorkerResource($worker->load('employerOrganization')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a worker.
     *
     * @authenticated
     */
    public function update(UpdateWorkerRequest $request, Worker $worker): WorkerResource
    {
        $worker->update($request->validated());

        return new WorkerResource($worker->fresh()->load('employerOrganization'));
    }

    /**
     * Soft-delete a worker.
     *
     * @authenticated
     */
    public function destroy(Worker $worker): Response
    {
        $worker->delete();

        return response()->noContent();
    }

    /**
     * Get the consolidated e-Passport view: identity, employer, all valid certs,
     * medical fitness, induction status. Drives the worker detail screen and
     * is the read model the gate-scan flow projects from.
     *
     * @authenticated
     */
    public function passport(Worker $worker): JsonResponse
    {
        return response()->json([
            'data' => $this->workers->passport($worker),
        ]);
    }

    /**
     * Generate the helmet QR PNG for a worker.
     *
     * Returns image/png. Used by the print flow on the web admin and by mobile
     * if a worker needs a fresh sticker. Requires authenticated caller — token
     * never leaves the server in plain text.
     *
     * @authenticated
     */
    public function helmetQr(Worker $worker): Response
    {
        $png = $this->qrCodes->pngFromToken($worker->helmet_qr_token);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => sprintf('inline; filename="worker-%s-helmet.png"', $worker->id),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Generate the coverall QR PNG for a worker.
     *
     * @authenticated
     */
    public function coverallQr(Worker $worker): Response
    {
        $png = $this->qrCodes->pngFromToken($worker->coverall_qr_token);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => sprintf('inline; filename="worker-%s-coverall.png"', $worker->id),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Bulk-import workers.
     *
     * Accepts {"workers": [{...}, ...]}. Per-record success/failure: a single
     * record's validation failure does NOT fail the rest of the batch. Each
     * record is validated against the same rules as the single-record POST.
     *
     * Idempotency-Key supported globally; replays return the same response.
     *
     * @authenticated
     */
    public function bulkImport(BulkWorkerRequest $request): JsonResponse
    {
        $result = $this->bulk->importMany($request->validated('workers'));

        // 207 Multi-Status if any record failed; 201 if all succeeded.
        $status = $result['summary']['failed'] > 0 ? 207 : 201;

        return response()->json($result, $status);
    }
}
