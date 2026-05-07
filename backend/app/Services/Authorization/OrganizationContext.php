<?php

namespace App\Services\Authorization;

use App\Models\Engagement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Resolves the set of organization IDs the authenticated user is allowed to
 * see data from, given their role on each org.
 *
 * Multi-party rules (per Doc 1):
 *   - client (project owner): sees workers/equipment of any org engaged on
 *     a project owned by their org, plus their own.
 *   - main_contractor: sees own org's data + the data of subcontractors
 *     engaged under them on shared projects.
 *   - consultant: sees data on every project they supervise (cross-org).
 *   - subcontractor: sees own org only (narrow).
 *
 * The active organization comes from:
 *   1. ?organization_id= query param if the user belongs to that org
 *   2. otherwise the user's first org membership
 *
 * The class is request-scoped via the service container; controllers call
 * accessibleOrganizationIds() and use that as a whereIn filter on lists.
 */
class OrganizationContext
{
    private ?User $user = null;

    private ?Organization $activeOrganization = null;

    /** @var array<int, string>|null */
    private ?array $accessibleIds = null;

    public function forRequest(Request $request): self
    {
        $clone = new self;
        $clone->user = $request->user();
        $clone->activeOrganization = $clone->resolveActiveOrganization($request);

        return $clone;
    }

    public function activeOrganization(): ?Organization
    {
        return $this->activeOrganization;
    }

    /**
     * Org IDs the current user is allowed to see DATA from on list endpoints.
     *
     * @return array<int, string>
     */
    public function accessibleOrganizationIds(): array
    {
        if ($this->accessibleIds !== null) {
            return $this->accessibleIds;
        }

        $user = $this->user;
        $active = $this->activeOrganization;

        if ($user === null || $active === null) {
            return $this->accessibleIds = [];
        }

        $ids = collect([$active->id]);

        switch ($active->default_role) {
            case Organization::ROLE_CLIENT:
                // All orgs engaged on projects owned by this client
                $projectIds = $active->ownedProjects()->pluck('id')->all();
                $engagedIds = Engagement::whereIn('project_id', $projectIds)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('organization_id');
                $ids = $ids->merge($engagedIds);
                break;

            case Organization::ROLE_MAIN_CONTRACTOR:
                // Own subcontractors (engagements where parent_engagement is
                // ours, on any project)
                $myEngagementIds = Engagement::where('organization_id', $active->id)->pluck('id');
                $subIds = Engagement::whereIn('parent_engagement_id', $myEngagementIds)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('organization_id');
                $ids = $ids->merge($subIds);
                break;

            case Organization::ROLE_CONSULTANT:
                // All orgs on projects this consultant supervises
                $supervisedProjectIds = Engagement::where('organization_id', $active->id)
                    ->where('role', Engagement::ROLE_CONSULTANT)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('project_id')->unique();
                $orgsOnProjects = Engagement::whereIn('project_id', $supervisedProjectIds)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('organization_id');
                $ids = $ids->merge($orgsOnProjects);
                break;

            case Organization::ROLE_SUBCONTRACTOR:
                // Narrow scope: self only
                break;
        }

        return $this->accessibleIds = $ids->unique()->values()->all();
    }

    /**
     * Project IDs the current user is allowed to see PROJECT-LEVEL data from
     * (permits, hazard reports). Used where filtering must be project-scoped
     * rather than org-scoped (e.g. a sub working on Project A shouldn't see
     * permits on Project B even though they're the same employer).
     *
     * @return array<int, string>
     */
    public function accessibleProjectIds(): array
    {
        $active = $this->activeOrganization;
        if ($active === null) {
            return [];
        }

        switch ($active->default_role) {
            case Organization::ROLE_CLIENT:
                return $active->ownedProjects()->pluck('id')->all();

            case Organization::ROLE_CONSULTANT:
                return Engagement::where('organization_id', $active->id)
                    ->where('role', Engagement::ROLE_CONSULTANT)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('project_id')->unique()->values()->all();

            case Organization::ROLE_MAIN_CONTRACTOR:
            case Organization::ROLE_SUBCONTRACTOR:
                return Engagement::where('organization_id', $active->id)
                    ->where('status', Engagement::STATUS_ACTIVE)
                    ->pluck('project_id')->unique()->values()->all();
        }

        return [];
    }

    private function resolveActiveOrganization(Request $request): ?Organization
    {
        if ($this->user === null) {
            return null;
        }

        $explicit = $request->query('organization_id') ?: $request->header('X-Organization-Id');
        $org = $explicit
            ? $this->user->organizations()->where('organizations.id', $explicit)->first()
            : $this->user->organizations()->first();

        // belongsToMany returns Model with a pivot mixin; coerce to the
        // concrete Organization for the type contract.
        return $org instanceof Organization ? $org : null;
    }
}
