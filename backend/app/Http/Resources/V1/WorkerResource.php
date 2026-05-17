<?php

namespace App\Http\Resources\V1;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Worker
 */
class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employer_organization_id' => $this->employer_organization_id,
            'employer_organization' => $this->whenLoaded('employerOrganization', fn () => [
                'id' => $this->employerOrganization->id,
                'name_en' => $this->employerOrganization->name_en,
                'name_ar' => $this->employerOrganization->name_ar,
            ]),
            'employee_id' => $this->employee_id,
            'national_id' => $this->national_id,
            'iqama_number' => $this->iqama_number,
            'passport_number' => $this->passport_number,
            'first_name_en' => $this->first_name_en,
            'last_name_en' => $this->last_name_en,
            'first_name_ar' => $this->first_name_ar,
            'last_name_ar' => $this->last_name_ar,
            'full_name_en' => $this->full_name_en,
            'full_name_ar' => $this->full_name_ar,
            'nationality' => $this->nationality,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'phone' => $this->phone,
            'email' => $this->email,
            'trade' => $this->trade,
            'induction_status' => $this->induction_status,
            'induction_date' => optional($this->induction_date)->toDateString(),
            'induction_valid_until' => optional($this->induction_valid_until)->toDateString(),
            'photo_path' => $this->photo_url,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
