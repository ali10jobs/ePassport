<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Permit
 */
class PermitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'permit_number' => $this->permit_number,
            'project_id' => $this->project_id,
            'site_id' => $this->site_id,
            'issuing_organization_id' => $this->issuing_organization_id,
            'permit_type_id' => $this->permit_type_id,
            'permit_type' => $this->whenLoaded('permitType', fn () => [
                'id' => $this->permitType->id,
                'code' => $this->permitType->code,
                'name_en' => $this->permitType->name_en,
                'name_ar' => $this->permitType->name_ar,
            ]),
            'status' => $this->status,
            'scope_en' => $this->scope_en,
            'scope_ar' => $this->scope_ar,
            'location_description_en' => $this->location_description_en,
            'location_description_ar' => $this->location_description_ar,
            'valid_from' => $this->valid_from?->toIso8601String(),
            'valid_until' => $this->valid_until?->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'closed_at' => $this->closed_at?->toIso8601String(),
            'closure_notes' => $this->closure_notes,
            'workers_count' => $this->whenCounted('workers'),
            'equipment_count' => $this->whenCounted('equipment'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
