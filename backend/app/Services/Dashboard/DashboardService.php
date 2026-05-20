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
use Illuminate\Support\Facades\DB;

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
    /**
     * @param  array{expired_from?: string, expired_to?: string, expiring_from?: string, expiring_to?: string}  $certRanges
     */
    public function mainContractorSummary(Organization $contractorOrg, array $certRanges = []): array
    {
        $orgIds = [$contractorOrg->id];
        // Subcontractors engaged under this main contractor
        $subOrgIds = Engagement::where('status', Engagement::STATUS_ACTIVE)
            ->whereIn('parent_engagement_id', Engagement::where('organization_id', $contractorOrg->id)->pluck('id'))
            ->pluck('organization_id')->unique()->all();

        $allOrgIds = array_merge($orgIds, $subOrgIds);

        // workers.mine + workers.subs in one query (was 2)
        $workerOrgIdsForQuery = array_merge([$contractorOrg->id], $subOrgIds);
        $wRow = Worker::query()
            ->whereIn('employer_organization_id', $workerOrgIdsForQuery)
            ->selectRaw('COUNT(*) FILTER (WHERE employer_organization_id = ?) AS mine', [$contractorOrg->id])
            ->selectRaw($subOrgIds === [] ? '0 AS subs' : 'COUNT(*) FILTER (WHERE employer_organization_id <> ?) AS subs', $subOrgIds === [] ? [] : [$contractorOrg->id])
            ->first();

        return [
            'role' => 'main_contractor',
            'organization_id' => $contractorOrg->id,
            'subcontractors' => $subOrgIds,
            'workers' => [
                'mine' => (int) ($wRow?->mine ?? 0),
                'subs' => (int) ($wRow?->subs ?? 0),
            ],
            'equipment' => [
                'mine' => Equipment::where('owner_organization_id', $contractorOrg->id)->count(),
                'tpi_expired' => Equipment::where('owner_organization_id', $contractorOrg->id)
                    ->whereDoesntHave('certifications', fn ($q) => $q->where('expiry_date', '>=', now()->toDateString())
                        ->whereIn('result', ['pass', 'pass_with_conditions']))
                    ->count(),
            ],
            'certifications' => $this->certExpiryCounts($allOrgIds, $certRanges),
            'permits' => $this->permitCountsForOrg($contractorOrg->id, withRecentlyRejected: true),
            'hazards' => $this->hazardCountsForAssignedOrg($contractorOrg->id),
            'scans' => $this->scanCountsLast24h(),
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
        $w = Worker::query()
            ->where('employer_organization_id', $subOrg->id)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE induction_status = ?) AS inducted', [Worker::INDUCTION_INDUCTED])
            ->selectRaw('COUNT(*) FILTER (WHERE induction_status <> ?) AS not_inducted', [Worker::INDUCTION_INDUCTED])
            ->first();

        return [
            'role' => 'subcontractor',
            'organization_id' => $subOrg->id,
            'workers' => [
                'total' => (int) ($w?->total ?? 0),
                'inducted' => (int) ($w?->inducted ?? 0),
                'not_inducted' => (int) ($w?->not_inducted ?? 0),
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
     * When $ranges contains expired_from/expired_to or expiring_from/expiring_to
     * the corresponding *_in_range count is computed using those bounds (both
     * required to activate a range — partial inputs are ignored).
     *
     * @param  array<int, string>  $employerOrgIds
     * @param  array{expired_from?: string, expired_to?: string, expiring_from?: string, expiring_to?: string}  $ranges
     * @return array<string, int|null>
     */
    private function certExpiryCounts(array $employerOrgIds, array $ranges = []): array
    {
        if ($employerOrgIds === []) {
            return [
                'expired' => 0, 'expiring_30_days' => 0, 'expiring_60_days' => 0, 'expiring_90_days' => 0,
                'expired_in_range' => null, 'expiring_in_range' => null,
            ];
        }

        $today = Carbon::now()->startOfDay()->toDateString();
        $in30 = Carbon::now()->addDays(30)->toDateString();
        $in60 = Carbon::now()->addDays(60)->toDateString();
        $in90 = Carbon::now()->addDays(90)->toDateString();

        // Single roll-up across all expiry buckets. The previous version cloned
        // the base query and ran a COUNT per bucket → 4-6 separate Postgres
        // round trips.
        $query = WorkerCertification::query()
            ->whereHas('worker', fn ($q) => $q->whereIn('employer_organization_id', $employerOrgIds))
            ->whereNotNull('expiry_date')
            ->selectRaw('COUNT(*) FILTER (WHERE expiry_date < ?) AS expired', [$today])
            ->selectRaw('COUNT(*) FILTER (WHERE expiry_date BETWEEN ? AND ?) AS expiring_30_days', [$today, $in30])
            ->selectRaw('COUNT(*) FILTER (WHERE expiry_date BETWEEN ? AND ?) AS expiring_60_days', [$today, $in60])
            ->selectRaw('COUNT(*) FILTER (WHERE expiry_date BETWEEN ? AND ?) AS expiring_90_days', [$today, $in90]);

        $hasExpiredRange = ! empty($ranges['expired_from']) && ! empty($ranges['expired_to']);
        $hasExpiringRange = ! empty($ranges['expiring_from']) && ! empty($ranges['expiring_to']);

        if ($hasExpiredRange) {
            [$lo, $hi] = self::normalizeRange($ranges['expired_from'], $ranges['expired_to']);
            $query->selectRaw(
                'COUNT(*) FILTER (WHERE expiry_date < ? AND expiry_date BETWEEN ? AND ?) AS expired_in_range',
                [$today, $lo, $hi]
            );
        }
        if ($hasExpiringRange) {
            [$lo, $hi] = self::normalizeRange($ranges['expiring_from'], $ranges['expiring_to']);
            $query->selectRaw(
                'COUNT(*) FILTER (WHERE expiry_date >= ? AND expiry_date BETWEEN ? AND ?) AS expiring_in_range',
                [$today, $lo, $hi]
            );
        }

        $row = $query->first();

        return [
            'expired' => (int) ($row?->expired ?? 0),
            'expiring_30_days' => (int) ($row?->expiring_30_days ?? 0),
            'expiring_60_days' => (int) ($row?->expiring_60_days ?? 0),
            'expiring_90_days' => (int) ($row?->expiring_90_days ?? 0),
            'expired_in_range' => $hasExpiredRange ? (int) ($row?->expired_in_range ?? 0) : null,
            'expiring_in_range' => $hasExpiringRange ? (int) ($row?->expiring_in_range ?? 0) : null,
        ];
    }

    /**
     * Return [low, high] regardless of input order so reversed date pickers
     * (UI lets users pick "from" later than "to") still produce a valid
     * SQL BETWEEN clause.
     *
     * @return array{0: string, 1: string}
     */
    private static function normalizeRange(string $a, string $b): array
    {
        return strcmp($a, $b) <= 0 ? [$a, $b] : [$b, $a];
    }

    /** @param array<int, string> $projectIds */
    private function permitCountsForProjects(array $projectIds): array
    {
        if ($projectIds === []) {
            return ['active_approved' => 0, 'awaiting_review' => 0, 'closed_this_week' => 0];
        }
        $row = Permit::query()
            ->whereIn('project_id', $projectIds)
            ->selectRaw("COUNT(*) FILTER (WHERE status = ?) AS active_approved", [Permit::STATUS_APPROVED])
            ->selectRaw("COUNT(*) FILTER (WHERE status = ?) AS awaiting_review", [Permit::STATUS_SUBMITTED])
            ->selectRaw("COUNT(*) FILTER (WHERE status = ? AND closed_at >= ?) AS closed_this_week", [Permit::STATUS_CLOSED, now()->startOfWeek()])
            ->first();

        return [
            'active_approved' => (int) ($row?->active_approved ?? 0),
            'awaiting_review' => (int) ($row?->awaiting_review ?? 0),
            'closed_this_week' => (int) ($row?->closed_this_week ?? 0),
        ];
    }

    private function permitCountsForOrg(string $orgId, bool $withRecentlyRejected = false): array
    {
        $query = Permit::query()
            ->where('issuing_organization_id', $orgId)
            ->selectRaw("COUNT(*) FILTER (WHERE status = ?) AS active_approved", [Permit::STATUS_APPROVED])
            ->selectRaw("COUNT(*) FILTER (WHERE status = ?) AS drafts", [Permit::STATUS_DRAFT])
            ->selectRaw("COUNT(*) FILTER (WHERE status = ?) AS awaiting_review", [Permit::STATUS_SUBMITTED]);

        if ($withRecentlyRejected) {
            $query->selectRaw(
                "COUNT(*) FILTER (WHERE status = ? AND rejected_at >= ?) AS recently_rejected",
                [Permit::STATUS_REJECTED, now()->subDays(7)]
            );
        }

        $row = $query->first();

        $out = [
            'active_approved' => (int) ($row?->active_approved ?? 0),
            'drafts' => (int) ($row?->drafts ?? 0),
            'awaiting_review' => (int) ($row?->awaiting_review ?? 0),
        ];
        if ($withRecentlyRejected) {
            $out['recently_rejected'] = (int) ($row?->recently_rejected ?? 0);
        }

        return $out;
    }

    /** @param array<int, string> $projectIds */
    private function hazardCountsForProjects(array $projectIds): array
    {
        if ($projectIds === []) {
            return ['submitted_mtd' => 0, 'open_critical' => 0, 'resolved_mtd' => 0];
        }
        $startOfMonth = now()->startOfMonth();
        $row = HazardReport::query()
            ->whereIn('project_id', $projectIds)
            ->selectRaw("COUNT(*) FILTER (WHERE created_at >= ?) AS submitted_mtd", [$startOfMonth])
            ->selectRaw(
                "COUNT(*) FILTER (WHERE severity = ? AND status NOT IN (?, ?)) AS open_critical",
                [HazardReport::SEVERITY_CRITICAL, HazardReport::STATUS_RESOLVED, HazardReport::STATUS_DISMISSED]
            )
            ->selectRaw(
                "COUNT(*) FILTER (WHERE status = ? AND resolved_at >= ?) AS resolved_mtd",
                [HazardReport::STATUS_RESOLVED, $startOfMonth]
            )
            ->first();

        return [
            'submitted_mtd' => (int) ($row?->submitted_mtd ?? 0),
            'open_critical' => (int) ($row?->open_critical ?? 0),
            'resolved_mtd' => (int) ($row?->resolved_mtd ?? 0),
        ];
    }

    private function hazardCountsForAssignedOrg(string $orgId): array
    {
        $row = HazardReport::query()
            ->where('assigned_to_organization_id', $orgId)
            ->selectRaw("COUNT(*) AS assigned_to_us")
            ->selectRaw(
                "COUNT(*) FILTER (WHERE status NOT IN (?, ?)) AS open_assigned_to_us",
                [HazardReport::STATUS_RESOLVED, HazardReport::STATUS_DISMISSED]
            )
            ->first();

        return [
            'assigned_to_us' => (int) ($row?->assigned_to_us ?? 0),
            'open_assigned_to_us' => (int) ($row?->open_assigned_to_us ?? 0),
        ];
    }

    private function scanCountsLast24h(): array
    {
        // Single FILTER-aggregation query instead of 4 separate COUNT trips.
        // Postgres rolls all four counts off one scan_events scan; saves
        // ~3 round-trip ms on each dashboard request.
        $row = ScanEvent::query()
            ->where('scanned_at', '>=', now()->subDay())
            ->selectRaw('COUNT(*) AS total_24h')
            ->selectRaw("COUNT(*) FILTER (WHERE result = ?) AS green_24h", [ScanEvent::RESULT_GREEN])
            ->selectRaw("COUNT(*) FILTER (WHERE result = ?) AS red_24h", [ScanEvent::RESULT_RED])
            ->selectRaw("COUNT(*) FILTER (WHERE result = ?) AS impersonation_24h", [ScanEvent::RESULT_IMPERSONATION_FLAG])
            ->first();

        return [
            'total_24h' => (int) ($row?->total_24h ?? 0),
            'green_24h' => (int) ($row?->green_24h ?? 0),
            'red_24h' => (int) ($row?->red_24h ?? 0),
            'impersonation_24h' => (int) ($row?->impersonation_24h ?? 0),
        ];
    }
}
