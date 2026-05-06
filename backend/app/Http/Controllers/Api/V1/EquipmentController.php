<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AttachEquipmentCertificationRequest;
use App\Http\Requests\V1\PairEquipmentOperatorRequest;
use App\Http\Requests\V1\StoreEquipmentRequest;
use App\Http\Requests\V1\UpdateEquipmentRequest;
use App\Http\Resources\V1\EquipmentResource;
use App\Models\Equipment;
use App\Models\Worker;
use App\Services\Equipment\EquipmentService;
use App\Services\QrCode\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Equipment
 */
class EquipmentController extends Controller
{
    public function __construct(
        private readonly EquipmentService $equipment,
        private readonly QrCodeService $qrCodes,
    ) {
    }

    /**
     * List equipment.
     *
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = QueryBuilder::for(Equipment::class)
            ->allowedFilters([
                AllowedFilter::exact('owner_organization_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $like = '%'.$value.'%';
                    $query->where(function ($q) use ($like) {
                        $q->where('asset_tag', 'ilike', $like)
                            ->orWhere('serial_number', 'ilike', $like)
                            ->orWhere('manufacturer', 'ilike', $like)
                            ->orWhere('model', 'ilike', $like);
                    });
                }),
                AllowedFilter::callback('tpi_status', function ($query, $value) {
                    if ($value === 'expired') {
                        $query->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())->whereIn('result', ['pass', 'pass_with_conditions']));
                    } elseif ($value === 'valid') {
                        $query->whereHas('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())->whereIn('result', ['pass', 'pass_with_conditions']));
                    }
                }),
            ])
            ->allowedSorts(['created_at', 'asset_tag', 'type'])
            ->allowedIncludes(['ownerOrganization', 'latestCertification'])
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->appends($request->query());

        return EquipmentResource::collection($items);
    }

    /**
     * @authenticated
     */
    public function show(Equipment $equipment): EquipmentResource
    {
        $equipment->load(['ownerOrganization', 'latestCertification']);

        return new EquipmentResource($equipment);
    }

    /**
     * @authenticated
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $eq = $this->equipment->create($request->validated());

        return (new EquipmentResource($eq->load('ownerOrganization')))->response()->setStatusCode(201);
    }

    /**
     * @authenticated
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        $equipment->update($request->validated());

        return new EquipmentResource($equipment->fresh()->load('ownerOrganization'));
    }

    /**
     * @authenticated
     */
    public function destroy(Equipment $equipment): Response
    {
        $equipment->delete();

        return response()->noContent();
    }

    /**
     * Equipment QR PNG (image/png). Used at print time for adhesive labels.
     *
     * @authenticated
     */
    public function qr(Equipment $equipment): Response
    {
        $png = $this->qrCodes->pngFromToken($equipment->qr_token);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => sprintf('inline; filename="equipment-%s.png"', $equipment->id),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Attach a TPI inspection certificate.
     *
     * @authenticated
     */
    public function attachCertification(AttachEquipmentCertificationRequest $request, Equipment $equipment): JsonResponse
    {
        $cert = $equipment->certifications()->create($request->validated());

        return response()->json([
            'data' => [
                'id' => $cert->id,
                'inspection_date' => optional($cert->inspection_date)->toDateString(),
                'expiry_date' => optional($cert->expiry_date)->toDateString(),
                'result' => $cert->result,
                'tpi_body_en' => $cert->tpi_body_en,
                'is_valid' => $cert->isValid(),
            ],
        ], 201);
    }

    /**
     * Pair an authorized operator with this equipment. Validates that the
     * worker holds at least one operator-relevant certification before
     * allowing the pairing. (For week 1 we accept any cert; per-equipment-type
     * required certs come later when we wire the equipment-type catalog.)
     *
     * @authenticated
     */
    public function pairOperator(PairEquipmentOperatorRequest $request, Equipment $equipment): JsonResponse
    {
        $worker = Worker::findOrFail($request->validated('worker_id'));

        // Sanity check: don't pair an operator who has zero certs at all.
        if ($worker->certifications()->count() === 0) {
            throw new ApiException(
                errorCode: ErrorCodes::OPERATOR_NOT_AUTHORIZED,
                message: 'Worker has no certifications on record and cannot be paired as an authorized operator.',
                status: 422,
                details: ['worker_id' => $worker->id],
            );
        }

        $pairing = $equipment->operatorPairings()->create([
            'worker_id' => $worker->id,
            'valid_from' => $request->validated('valid_from'),
            'valid_until' => $request->validated('valid_until'),
            'authorized_by_user_id' => $request->user()?->id,
            'authorized_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $pairing->id,
                'worker_id' => $pairing->worker_id,
                'equipment_id' => $pairing->equipment_id,
                'valid_from' => optional($pairing->valid_from)->toDateString(),
                'valid_until' => optional($pairing->valid_until)->toDateString(),
                'is_currently_valid' => $pairing->isCurrentlyValid(),
            ],
        ], 201);
    }
}
