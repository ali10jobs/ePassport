<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PermitType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group Permit Types
 *
 * Catalog endpoint for the permit-type list (HOT_WORK, CONFINED_SPACE, etc.).
 * Used by the Permit create form to populate the type selector.
 */
class PermitTypeController extends Controller
{
    /**
     * @authenticated
     */
    public function index(): AnonymousResourceCollection
    {
        $types = PermitType::query()
            ->where('is_active', true)
            ->orderBy('name_en')
            ->get();

        return JsonResource::collection($types->map(fn (PermitType $t) => [
            'id' => $t->id,
            'code' => $t->code,
            'name_en' => $t->name_en,
            'name_ar' => $t->name_ar,
            'description_en' => $t->description_en,
            'description_ar' => $t->description_ar,
            'requires_consultant_approval' => (bool) $t->requires_consultant_approval,
            'requires_gas_test' => (bool) $t->requires_gas_test,
            'requires_fire_watch' => (bool) $t->requires_fire_watch,
            'default_validity_hours' => $t->default_validity_hours,
        ]));
    }
}
