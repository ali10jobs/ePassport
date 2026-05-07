<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\UserOrganizationRole;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Dashboards
 *
 * Role-based dashboard summaries. Each route picks the user's active org
 * (first matching the requested role) and computes the metrics.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboards)
    {
    }

    /**
     * Client dashboard: cross-project metrics across all owned projects.
     *
     * @authenticated
     */
    public function client(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_CLIENT);
        return response()->json(['data' => $this->dashboards->clientSummary($org)]);
    }

    /**
     * Main contractor dashboard.
     *
     * @authenticated
     */
    public function mainContractor(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_MAIN_CONTRACTOR);
        return response()->json(['data' => $this->dashboards->mainContractorSummary($org)]);
    }

    /**
     * Consultant dashboard.
     *
     * @authenticated
     */
    public function consultant(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_CONSULTANT);
        return response()->json(['data' => $this->dashboards->consultantSummary($org)]);
    }

    /**
     * Subcontractor dashboard.
     *
     * @authenticated
     */
    public function subcontractor(Request $request): JsonResponse
    {
        $org = $this->resolveOrgForUser($request, Organization::ROLE_SUBCONTRACTOR);
        return response()->json(['data' => $this->dashboards->subcontractorSummary($org)]);
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
