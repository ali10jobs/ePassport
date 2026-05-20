<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group Dashboards
 *
 * Role-based dashboard summaries. Each route picks the user's active org
 * (first matching the requested role) and computes the metrics.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboards) {}

    /**
     * Cache window for dashboard payloads. Numbers refresh every 30s — short
     * enough to feel live for the mobile/web UI, long enough that rapid
     * navigation and polling collapse into a single DB hit per window.
     */
    private const SUMMARY_TTL_SECONDS = 30;

    /**
     * Client dashboard: cross-project metrics across all owned projects.
     *
     * @authenticated
     */
    public function client(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_CLIENT);

        $data = Cache::remember(
            "dash:client:{$org->id}",
            self::SUMMARY_TTL_SECONDS,
            fn () => $this->dashboards->clientSummary($org),
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Main contractor dashboard.
     *
     * @authenticated
     */
    public function mainContractor(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_MAIN_CONTRACTOR);

        $certRanges = array_filter([
            'expired_from' => $request->query('expired_from'),
            'expired_to' => $request->query('expired_to'),
            'expiring_from' => $request->query('expiring_from'),
            'expiring_to' => $request->query('expiring_to'),
        ], fn ($v) => $v !== null && $v !== '');

        $rangesKey = $certRanges === [] ? 'none' : md5(json_encode($certRanges));
        $data = Cache::remember(
            "dash:maincon:{$org->id}:{$rangesKey}",
            self::SUMMARY_TTL_SECONDS,
            fn () => $this->dashboards->mainContractorSummary($org, $certRanges),
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Consultant dashboard.
     *
     * @authenticated
     */
    public function consultant(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_CONSULTANT);

        $data = Cache::remember(
            "dash:consultant:{$org->id}",
            self::SUMMARY_TTL_SECONDS,
            fn () => $this->dashboards->consultantSummary($org),
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Subcontractor dashboard.
     *
     * @authenticated
     */
    public function subcontractor(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_SUBCONTRACTOR);

        $data = Cache::remember(
            "dash:subcon:{$org->id}",
            self::SUMMARY_TTL_SECONDS,
            fn () => $this->dashboards->subcontractorSummary($org),
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Resolve the active organization for the authenticated user matching the
     * requested role. If the caller passes ?organization_id=... we honour it
     * if they belong to that org; otherwise pick the first matching org.
     */
    private function resolveOrgForUser(Request $request, string $orgRole): Organization
    {
        $user = $request->user();

        $explicitOrgId = $request->query('organization_id');
        if ($explicitOrgId !== null) {
            $org = $user->organizations()
                ->where('organizations.id', $explicitOrgId)
                ->where('organizations.default_role', $orgRole)
                ->first();
            if ($org === null) {
                throw new ApiException(
                    errorCode: ErrorCodes::ORG_NOT_ACCESSIBLE,
                    message: 'The requested organization is not accessible to the authenticated user, or its role does not match this dashboard.',
                    status: 403,
                );
            }

            return $org;
        }

        $org = $user->organizations()->where('organizations.default_role', $orgRole)->first();
        if ($org === null) {
            throw new ApiException(
                errorCode: ErrorCodes::ORG_CONTEXT_MISSING,
                message: "Authenticated user has no '{$orgRole}' organization membership.",
                status: 403,
            );
        }

        return $org;
    }
}
