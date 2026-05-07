<?php

namespace App\Services\Dashboard;

use App\Models\Engagement;
use App\Models\Equipment;
use App\Models\HazardReport;
use App\Models\Organization;
use App\Models\Permit;
use App\Models\ScanEvent;
use App\Models\Worker;
use App\Models\WorkerCertification;
use Illuminate\Support\Carbon;

/**
 * Computes role-scoped dashboard metrics. Each method returns a structured
 * payload with both numbers AND drill-down filter strings so the web UI
 * can build clickable list links from the same response.
 */
class DashboardService
{
    /**
     * Client (project owner) view: cross-contractor metrics for owned projects.
     *
     * @return array<string, mixed>
     */
    public function clientSummary(Organization $clientOrg): array
    {
        $projectIds = $clientOrg->ownedProjects()->pluck('id')->all();

        // Workers across all engaged contractors on owned projects
        $engagedOrgIds = Engagement::whereIn('project_id', $projectIds)
            ->where('status', Engagement::STATUS_ACTIVE)
            ->pluck('organization_id')
            ->unique()
            ->all();

        $workersTotal = Worker::whereIn('employer_organization_id', $engagedOrgIds)->count();
        $workersByOrg = Worker::whereIn('employer_organization_id', $engagedOrgIds)
            ->selectRaw('employer_organization_id, COUNT(*) as c')
            ->groupBy('employer_organization_id')
            ->pluck('c', 'employer_organization_id')
            ->all();

        return [
            'role' => 'client',
            'project_ids' => $projectIds,
            'workers' => [
                'total' => $workersTotal,
                'by_organization' => $workersByOrg,
            ],
            'certifications' => $this->certExpiryCounts($engagedOrgIds),
            'permits' => $this->permitCountsForProjects($projectIds),
            'hazards' => $this->hazardCountsForProjects($projectIds),
            'scans' => $this->scanCountsLast24h(),
            'incident_indicators' => [
                'red_scans_today' => ScanEvent::where('result', ScanEvent::RESULT_RED)
                    ->where('scanned_at', '>=', now()->startOfDay())->count(),
                'impersonation_flags_today' => ScanEvent::where('result', ScanEvent::RESULT_IMPERSONATION_FLAG)
                    ->where('scanned_at', '>=', now()->startOfDay())->count(),
                'critical_hazards_open' => HazardReport::where('severity', HazardReport::SEVERITY_CRITICAL)
                    ->whereNotIn('status', [HazardReport::STATUS_RESOLVED, HazardReport::STATUS_DISMISSED])
                    ->whereIn('project_id', $projectIds)->count(),
            ],
        ];
    }

    /**
     * Main contractor view: scoped to their own org's workers/equipment/permits.
     *
     * @return array<string, mixed>
     */
    public function mainContractorSummary(Organization $contractorOrg): array
    {
        $orgIds = [$contractorOrg->id];
        // Subcontractors engaged under this main contractor
        $subOrgIds = Engagement::where('status', Engagement::STATUS_ACTIVE)
            ->whereIn('parent_engagement_id', Engagement::where('organization_id', $contractorOrg->id)->pluck('id'))
            ->pluck('organization_id')->unique()->all();

        $allOrgIds = array_merge($orgIds, $subOrgIds);

        return [
            'role' => 'main_contractor',
            'organization_id' => $contractorOrg->id,
            'subcontractors' => $subOrgIds,
            'workers' => [
                'mine' => Worker::where('employer_organization_id', $contractorOrg->id)->count(),
                'subs' => Worker::whereIn('employer_organization_id', $subOrgIds)->count(),
            ],
            'equipment' => [
                'mine' => Equipment::where('owner_organization_id', $contractorOrg->id)->count(),
                'tpi_expired' => Equipment::where('owner_organization_id', $contractorOrg->id)
                    ->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())
                        ->whereIn('result', ['pass', 'pass_with_conditions']))
                    ->count(),
            ],
            'certifications' => $this->certExpiryCounts($allOrgIds),
            'permits' => array_merge(
                $this->permitCountsForOrg($contractorOrg->id),
                ['recently_rejected' => Permit::where('issuing_organization_id', $contractorOrg->id)
                    ->where('status', Permit::STATUS_REJECTED)
                    ->where('rejected_at', '>=', now()->subDays(7))
                    ->count()],
            ),
            'hazards' => $this->hazardCountsForAssignedOrg($contractorOrg->id),
        ];
    }

    /**
     * Consultant view: permits awaiting review + supervised hazard reports.
     *
     * @return array<string, mixed>
     */
    public function consultantSummary(Organization $consultantOrg): array
    {
        // Projects this consultant is engaged on
        $projectIds = Engagement::where('organization_id', $consultantOrg->id)
            ->where('role', Engagement::ROLE_CONSULTANT)
            ->where('status', Engagement::STATUS_ACTIVE)
            ->pluck('project_id')->unique()->all();

        $permitsAwaiting = Permit::where('status', Permit::STATUS_SUBMITTED)
            ->whereIn('project_id', $projectIds)
            ->orderBy('submitted_at')
            ->limit(10)
            ->get(['id', 'permit_number', 'submitted_at', 'permit_type_id', 'project_id']);

        return [
            'role' => 'consultant',
            'organization_id' => $consultantOrg->id,
            'project_ids' => $projectIds,
            'permits' => [
                'awaiting_review' => Permit::where('status', Permit::STATUS_SUBMITTED)->whereIn('project_id', $projectIds)->count(),
                'approved_today' => Permit::where('status', Permit::STATUS_APPROVED)->where('approved_at', '>=', now()->startOfDay())->whereIn('project_id', $projectIds)->count(),
                'rejected_today' => Permit::where('status', Permit::STATUS_REJECTED)->where('rejected_at', '>=', now()->startOfDay())->whereIn('project_id', $projectIds)->count(),
                'top_awaiting' => $permitsAwaiting->map(fn ($p) => [
                    'id' => $p->id,
                    'permit_number' => $p->permit_number,
                    'submitted_at' => $p->submitted_at?->toIso8601String(),
                    'permit_type_id' => $p->permit_type_id,
                ])->all(),
            ],
            'hazards' => $this->hazardCountsForProjects($projectIds),
            'scans' => array_merge(
                $this->scanCountsLast24h(),
                ['red_scans_today' => ScanEvent::where('result', ScanEvent::RESULT_RED)->where('scanned_at', '>=', now()->startOfDay())->count()],
            ),
        ];
    }

    /**
     * Subcontractor view: own workers + own equipment + permits they're named on.
     *
     * @return array<string, mixed>
     */
    public function subcontractorSummary(Organization $subOrg): array
    {
        return [
            'role' => 'subcontractor',
            'organization_id' => $subOrg->id,
            'workers' => [
                'total' => Worker::where('employer_organization_id', $subOrg->id)->count(),
                'inducted' => Worker::where('employer_organization_id', $subOrg->id)->where('induction_status', Worker::INDUCTION_INDUCTED)->count(),
                'not_inducted' => Worker::where('employer_organization_id', $subOrg->id)->where('induction_status', '!=', Worker::INDUCTION_INDUCTED)->count(),
            ],
            'equipment' => [
                'mine' => Equipment::where('owner_organization_id', $subOrg->id)->count(),
            ],
            'certifications' => $this->certExpiryCounts([$subOrg->id]),
        ];
    }

    /**
     * Cert expiry counts in 30/60/90 day buckets, scoped to the given employer orgs.
     *
     * @param  array<int, string>  $employerOrgIds
     * @return array<string, int>
     */
    private function certExpiryCounts(array $employerOrgIds): array
    {
        $base = WorkerCertification::query()
            ->whereHas('worker', fn ($q) => $q->whereIn('employer_organization_id', $employerOrgIds))
            ->whereNotNull('expiry_date');

        $today = Carbon::now()->startOfDay()->toDateString();

        return [
            'expired' => (clone $base)->where('expiry_date', '<', $today)->count(),
            'expiring_30_days' => (clone $base)->whereBetween('expiry_date', [$today, Carbon::now()->addDays(30)->toDateString()])->count(),
            'expiring_60_days' => (clone $base)->whereBetween('expiry_date', [$today, Carbon::now()->addDays(60)->toDateString()])->count(),
            'expiring_90_days' => (clone $base)->whereBetween('expiry_date', [$today, Carbon::now()->addDays(90)->toDateString()])->count(),
        ];
    }

    /** @param array<int, string> $projectIds */
    private function permitCountsForProjects(array $projectIds): array
    {
        return [
            'active_approved' => Permit::whereIn('project_id', $projectIds)->where('status', Permit::STATUS_APPROVED)->count(),
            'awaiting_review' => Permit::whereIn('project_id', $projectIds)->where('status', Permit::STATUS_SUBMITTED)->count(),
            'closed_this_week' => Permit::whereIn('project_id', $projectIds)->where('status', Permit::STATUS_CLOSED)->where('closed_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    private function permitCountsForOrg(string $orgId): array
    {
        return [
            'active_approved' => Permit::where('issuing_organization_id', $orgId)->where('status', Permit::STATUS_APPROVED)->count(),
            'drafts' => Permit::where('issuing_organization_id', $orgId)->where('status', Permit::STATUS_DRAFT)->count(),
            'awaiting_review' => Permit::where('issuing_organization_id', $orgId)->where('status', Permit::STATUS_SUBMITTED)->count(),
        ];
    }

    /** @param array<int, string> $projectIds */
    private function hazardCountsForProjects(array $projectIds): array
    {
        $startOfMonth = now()->startOfMonth();

        return [
            'submitted_mtd' => HazardReport::whereIn('project_id', $projectIds)->where('created_at', '>=', $startOfMonth)->count(),
            'open_critical' => HazardReport::whereIn('project_id', $projectIds)
                ->where('severity', HazardReport::SEVERITY_CRITICAL)
                ->whereNotIn('status', [HazardReport::STATUS_RESOLVED, HazardReport::STATUS_DISMISSED])->count(),
            'resolved_mtd' => HazardReport::whereIn('project_id', $projectIds)
                ->where('status', HazardReport::STATUS_RESOLVED)
                ->where('resolved_at', '>=', $startOfMonth)->count(),
        ];
    }

    private function hazardCountsForAssignedOrg(string $orgId): array
    {
        return [
            'assigned_to_us' => HazardReport::where('assigned_to_organization_id', $orgId)->count(),
            'open_assigned_to_us' => HazardReport::where('assigned_to_organization_id', $orgId)
                ->whereNotIn('status', [HazardReport::STATUS_RESOLVED, HazardReport::STATUS_DISMISSED])->count(),
        ];
    }

    private function scanCountsLast24h(): array
    {
        $since = now()->subDay();
        return [
            'total_24h' => ScanEvent::where('scanned_at', '>=', $since)->count(),
            'green_24h' => ScanEvent::where('result', ScanEvent::RESULT_GREEN)->where('scanned_at', '>=', $since)->count(),
            'red_24h' => ScanEvent::where('result', ScanEvent::RESULT_RED)->where('scanned_at', '>=', $since)->count(),
            'impersonation_24h' => ScanEvent::where('result', ScanEvent::RESULT_IMPERSONATION_FLAG)->where('scanned_at', '>=', $since)->count(),
        ];
    }
}
