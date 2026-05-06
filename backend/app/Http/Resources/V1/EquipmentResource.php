<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Equipment
 */
class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_organization_id' => $this->owner_organization_id,
            'owner_organization' => $this->whenLoaded('ownerOrganization', fn () => [
                'id' => $this->ownerOrganization->id,
                'name_en' => $this->ownerOrganization->name_en,
                'name_ar' => $this->ownerOrganization->name_ar,
            ]),
            'asset_tag' => $this->asset_tag,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'type' => $this->type,
            'category' => $this->category,
            'manufacture_date' => optional($this->manufacture_date)->toDateString(),
            'safe_working_load_kg' => $this->safe_working_load_kg,
            'specs' => $this->specs,
            'metadata' => $this->metadata,
            'latest_certification' => $this->whenLoaded('latestCertification', fn () => $this->latestCertification ? [
                'id' => $this->latestCertification->id,
                'tpi_body_en' => $this->latestCertification->tpi_body_en,
                'inspection_date' => optional($this->latestCertification->inspection_date)->toDateString(),
                'expiry_date' => optional($this->latestCertification->expiry_date)->toDateString(),
                'result' => $this->latestCertification->result,
                'is_valid' => $this->latestCertification->isValid(),
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
