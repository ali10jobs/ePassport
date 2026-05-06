<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PermitResource;
use App\Models\Permit;
use App\Models\PermitEvent;
use App\Services\Permit\PermitService;
use App\Services\Permit\PermitValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @group Permits — Lifecycle
 *
 * Submit / approve / reject / close transitions, plus lifecycle history.
 * Each transition validates current status and writes an immutable PermitEvent.
 */
class PermitLifecycleController extends Controller
{
    public function __construct(
        private readonly PermitService $permits,
        private readonly PermitValidationService $validator,
    ) {
    }

    /**
     * Submit a draft permit. Re-runs all validation against named workers and
     * equipment. On failure returns 422 PERMIT_VALIDATION_FAILED with a full
     * per-worker / per-equipment breakdown so the UI can surface exactly which
     * worker has which expired cert.
     *
     * @authenticated
     */
    public function submit(Request $request, Permit $permit): JsonResponse
    {
        $this->ensureStatus($permit, [Permit::STATUS_DRAFT]);

        $validation = $this->validator->validateForSubmission($permit);

        if (! $validation['ok']) {
            // Log validation_failed event (does NOT change status)
            $this->permits->logEvent($permit, PermitEvent::TYPE_VALIDATION_FAILED, $request->user()?->id, [
                'worker_failures' => $validation['worker_failures'],
                'equipment_failures' => $validation['equipment_failures'],
                'project_failures' => $validation['project_failures'],
            ]);

            throw new ApiException(
                errorCode: ErrorCodes::PERMIT_VALIDATION_FAILED,
                message: 'Permit validation failed. Some named workers or equipment do not meet the requirements for this permit type.',
                status: 422,
                details: [
                    'worker_failures' => $validation['worker_failures'],
                    'equipment_failures' => $validation['equipment_failures'],
                    'project_failures' => $validation['project_failures'],
                ],
            );
        }

        DB::transaction(function () use ($permit, $request) {
            $permit->update([
                'status' => Permit::STATUS_SUBMITTED,
                'submitted_by_user_id' => $request->user()?->id,
                'submitted_at' => now(),
                'validation_snapshot' => ['ok' => true, 'validated_at' => now()->toIso8601String()],
            ]);

            $this->permits->logEvent($permit, PermitEvent::TYPE_SUBMITTED, $request->user()?->id);
            $this->permits->logEvent($permit, PermitEvent::TYPE_VALIDATED, $request->user()?->id);
        });

        return response()->json(['data' => (new PermitResource($permit->fresh()->load('permitType')))]);
    }

    /**
     * Approve a submitted permit (consultant role).
     *
     * @authenticated
     */
    public function approve(Request $request, Permit $permit): JsonResponse
    {
        $this->ensureStatus($permit, [Permit::STATUS_SUBMITTED]);

        $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($permit, $request) {
            $permit->update([
                'status' => Permit::STATUS_APPROVED,
                'approved_by_user_id' => $request->user()?->id,
                'approved_at' => now(),
            ]);

            $this->permits->logEvent($permit, PermitEvent::TYPE_APPROVED, $request->user()?->id, [], $request->input('comment'));
        });

        return response()->json(['data' => (new PermitResource($permit->fresh()->load('permitType')))]);
    }

    /**
     * Reject a submitted permit with mandatory reason.
     *
     * @authenticated
     */
    public function reject(Request $request, Permit $permit): JsonResponse
    {
        $this->ensureStatus($permit, [Permit::STATUS_SUBMITTED]);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        DB::transaction(function () use ($permit, $request, $validated) {
            $permit->update([
                'status' => Permit::STATUS_REJECTED,
                'rejected_by_user_id' => $request->user()?->id,
                'rejected_at' => now(),
                'rejection_reason' => $validated['reason'],
            ]);

            $this->permits->logEvent($permit, PermitEvent::TYPE_REJECTED, $request->user()?->id, [], $validated['reason']);
        });

        return response()->json(['data' => (new PermitResource($permit->fresh()->load('permitType')))]);
    }

    /**
     * Close an approved permit once work is complete.
     *
     * @authenticated
     */
    public function close(Request $request, Permit $permit): JsonResponse
    {
        $this->ensureStatus($permit, [Permit::STATUS_APPROVED]);

        $validated = $request->validate([
            'closure_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($permit, $request, $validated) {
            $permit->update([
                'status' => Permit::STATUS_CLOSED,
                'closed_by_user_id' => $request->user()?->id,
                'closed_at' => now(),
                'closure_notes' => $validated['closure_notes'] ?? null,
            ]);

            $this->permits->logEvent($permit, PermitEvent::TYPE_CLOSED, $request->user()?->id, [], $validated['closure_notes'] ?? null);
        });

        return response()->json(['data' => (new PermitResource($permit->fresh()->load('permitType')))]);
    }

    /**
     * Lifecycle history.
     *
     * @authenticated
     */
    public function events(Permit $permit): JsonResource
    {
        $events = $permit->events()->orderBy('occurred_at')->get();

        return JsonResource::collection($events->map(fn (PermitEvent $e) => [
            'id' => $e->id,
            'event_type' => $e->event_type,
            'actor_user_id' => $e->actor_user_id,
            'payload' => $e->payload,
            'comment' => $e->comment,
            'occurred_at' => $e->occurred_at?->toIso8601String(),
        ]));
    }

    /**
     * @param array<int, string> $allowedStatuses
     */
    private function ensureStatus(Permit $permit, array $allowedStatuses): void
    {
        if (! in_array($permit->status, $allowedStatuses, true)) {
            throw new ApiException(
                errorCode: ErrorCodes::PERMIT_INVALID_TRANSITION,
                message: sprintf(
                    "Cannot transition permit in '%s' status. Allowed: %s.",
                    $permit->status,
                    implode(', ', $allowedStatuses),
                ),
                status: 409,
                details: [
                    'current_status' => $permit->status,
                    'allowed_from' => $allowedStatuses,
                ],
            );
        }
    }
}
