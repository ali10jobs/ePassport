<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMedicalRecordRequest;
use App\Models\Worker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group Worker Medical Records
 *
 * Medical fitness history. The most recent record (by exam_date) drives the
 * MEDICAL_FAIL reason at gate scan. Historical records are retained for audit.
 */
class WorkerMedicalRecordController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Worker $worker): AnonymousResourceCollection
    {
        $records = $worker->medicalRecords()
            ->orderByDesc('exam_date')
            ->orderByDesc('created_at')
            ->get();

        return JsonResource::collection($records->map(fn ($r) => [
            'id' => $r->id,
            'exam_date' => optional($r->exam_date)->toDateString(),
            'valid_until' => optional($r->valid_until)->toDateString(),
            'status' => $r->status,
            'examining_clinic_en' => $r->examining_clinic_en,
            'examining_clinic_ar' => $r->examining_clinic_ar,
            'restrictions_en' => $r->restrictions_en,
            'restrictions_ar' => $r->restrictions_ar,
            'is_currently_fit' => $r->isFit(),
        ]));
    }

    /**
     * Add a new medical record. Most recent (by exam_date) becomes the active one.
     *
     * @authenticated
     */
    public function store(StoreMedicalRecordRequest $request, Worker $worker): JsonResponse
    {
        $record = $worker->medicalRecords()->create($request->validated());

        return response()->json([
            'data' => [
                'id' => $record->id,
                'exam_date' => optional($record->exam_date)->toDateString(),
                'valid_until' => optional($record->valid_until)->toDateString(),
                'status' => $record->status,
                'is_currently_fit' => $record->isFit(),
            ],
        ], 201);
    }
}
