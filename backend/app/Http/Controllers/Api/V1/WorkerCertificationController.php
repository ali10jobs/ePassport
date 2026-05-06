<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AttachWorkerCertificationRequest;
use App\Http\Resources\V1\WorkerCertificationResource;
use App\Models\Worker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Worker Certifications
 *
 * Manage a worker's certifications (NEBOSH, IOSH, scaffolding, etc.). The
 * status (valid / expiring_soon / expired) is computed dynamically from
 * expiry_date relative to today.
 */
class WorkerCertificationController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Worker $worker): AnonymousResourceCollection
    {
        return WorkerCertificationResource::collection(
            $worker->certifications()->with('certificationType')->orderByDesc('issue_date')->get()
        );
    }

    /**
     * Attach a certification to a worker.
     *
     * @authenticated
     */
    public function store(AttachWorkerCertificationRequest $request, Worker $worker): JsonResponse
    {
        $cert = $worker->certifications()->create($request->validated());

        return (new WorkerCertificationResource($cert->load('certificationType')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @authenticated
     */
    public function destroy(Worker $worker, string $certificationId): Response
    {
        $cert = $worker->certifications()->whereKey($certificationId)->firstOrFail();
        $cert->delete();

        return response()->noContent();
    }
}
