<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\VerifyEquipmentOperatorScanRequest;
use App\Http\Requests\V1\VerifyScanPairRequest;
use App\Http\Requests\V1\VerifyScanRequest;
use App\Models\ScanEvent;
use App\Services\Scan\ScanResult;
use App\Services\Scan\ScanVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Scan Verification
 *
 * The gate-scan flow. Each verify call writes one row to scan_events for the
 * audit trail; the response payload is what the client UI renders directly.
 */
class ScanController extends Controller
{
    public function __construct(
        private readonly ScanVerificationService $scans,
    ) {
    }

    /**
     * Verify a single scan (helmet, coverall, equipment, or manual employee_id).
     *
     * @authenticated
     */
    public function verify(VerifyScanRequest $request): JsonResponse
    {
        $context = array_filter([
            'project_id' => $request->validated('project_id'),
            'site_id' => $request->validated('site_id'),
        ]);

        if ($request->filled('employee_id')) {
            $result = $this->scans->verifyManualWorker($request->validated('employee_id'), $context);
            $tokenForLog = null;
        } else {
            $result = $this->scans->verifyToken($request->validated('token'), $context);
            $tokenForLog = $request->validated('token');
        }

        $event = $this->logEvent(
            request: $request,
            result: $result,
            tokenForLog: $tokenForLog,
            isManual: $request->filled('employee_id'),
        );

        return $this->respond($result, $event);
    }

    /**
     * Helmet + coverall cross-check. Two QRs in sequence; if they don't
     * resolve to the same worker the result is IMPERSONATION_FLAG.
     *
     * @authenticated
     */
    public function verifyPair(VerifyScanPairRequest $request): JsonResponse
    {
        $context = array_filter([
            'project_id' => $request->validated('project_id'),
            'site_id' => $request->validated('site_id'),
        ]);

        $result = $this->scans->verifyPair(
            $request->validated('helmet_token'),
            $request->validated('coverall_token'),
            $context,
        );

        $event = $this->logEvent(
            request: $request,
            result: $result,
            tokenForLog: $request->validated('helmet_token'),
            isManual: false,
            pairedScanData: [
                'mode' => 'helmet_coverall',
                'helmet_token_hash' => hash('sha256', $request->validated('helmet_token')),
                'coverall_token_hash' => hash('sha256', $request->validated('coverall_token')),
            ],
        );

        return $this->respond($result, $event);
    }

    /**
     * Equipment + paired operator. Verifies equipment TPI AND that the worker
     * is on the equipment's authorized operators list.
     *
     * @authenticated
     */
    public function verifyEquipmentOperator(VerifyEquipmentOperatorScanRequest $request): JsonResponse
    {
        $context = array_filter([
            'project_id' => $request->validated('project_id'),
            'site_id' => $request->validated('site_id'),
        ]);

        $result = $this->scans->verifyEquipmentWithOperator(
            $request->validated('equipment_token'),
            $request->validated('worker_token'),
            $context,
        );

        $event = $this->logEvent(
            request: $request,
            result: $result,
            tokenForLog: $request->validated('equipment_token'),
            isManual: false,
            pairedScanData: [
                'mode' => 'equipment_operator',
                'equipment_token_hash' => hash('sha256', $request->validated('equipment_token')),
                'worker_token_hash' => hash('sha256', $request->validated('worker_token')),
            ],
        );

        return $this->respond($result, $event);
    }

    /**
     * List scan events.
     *
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $events = QueryBuilder::for(ScanEvent::class)
            ->allowedFilters([
                AllowedFilter::exact('result'),
                AllowedFilter::exact('site_id'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::exact('subject_id'),
                AllowedFilter::exact('scanner_user_id'),
            ])
            ->allowedSorts(['scanned_at', 'created_at'])
            ->defaultSort('-scanned_at')
            ->paginate(min((int) $request->query('per_page', 50), 200))
            ->appends($request->query());

        return JsonResource::collection($events->through(fn (ScanEvent $e) => [
            'id' => $e->id,
            'scanner_user_id' => $e->scanner_user_id,
            'site_id' => $e->site_id,
            'subject_type' => $e->subject_type,
            'subject_id' => $e->subject_id,
            'token_type' => $e->scan_token_type,
            'result' => $e->result,
            'reasons' => $e->reasons,
            'is_manual_entry' => (bool) $e->is_manual_entry,
            'is_offline_originated' => (bool) $e->is_offline_originated,
            'client_app' => $e->client_app,
            'scanned_at' => $e->scanned_at?->toIso8601String(),
        ]));
    }

    /** @param array<string, mixed>|null $pairedScanData */
    private function logEvent(
        Request $request,
        ScanResult $result,
        ?string $tokenForLog,
        bool $isManual,
        ?array $pairedScanData = null,
    ): ScanEvent {
        return ScanEvent::create([
            'scanner_user_id' => $request->user()?->id,
            'site_id' => $request->input('site_id'),
            'subject_type' => $result->subjectType,
            'subject_id' => $result->subjectId,
            'scan_token_type' => $result->tokenType,
            // Scan tokens are sensitive; we store a hash, never the raw token in the log.
            'scan_token' => $tokenForLog !== null ? hash('sha256', $tokenForLog) : null,
            'result' => $result->result,
            'reasons' => $result->reasons,
            'paired_scan_data' => $pairedScanData,
            'is_manual_entry' => $isManual,
            'is_offline_originated' => false,
            'client_app' => $request->input('client_app', 'api'),
            'idempotency_key' => $request->header('Idempotency-Key'),
            'scanned_at' => now(),
        ]);
    }

    private function respond(ScanResult $result, ScanEvent $event): JsonResponse
    {
        return response()->json([
            'data' => array_merge($result->toArray(), [
                'event_id' => $event->id,
                'scanned_at' => $event->scanned_at->toIso8601String(),
            ]),
        ]);
    }
}
