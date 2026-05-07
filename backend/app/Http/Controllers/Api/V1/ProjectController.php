<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Authorization\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group Projects
 *
 * Lightweight catalog for the user's accessible projects. Drives the
 * Permit create form's project selector and any future project-scoped
 * filters on the web client.
 */
class ProjectController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Request $request, OrganizationContext $orgContext): AnonymousResourceCollection
    {
        $accessibleProjectIds = $orgContext->forRequest($request)->accessibleProjectIds();

        $projects = Project::query()
            ->whereIn('id', $accessibleProjectIds)
            ->orderBy('name_en')
            ->get();

        return JsonResource::collection($projects->map(fn (Project $p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name_en' => $p->name_en,
            'name_ar' => $p->name_ar,
            'status' => $p->status,
            'city' => $p->city,
            'region' => $p->region,
        ]));
    }
}
