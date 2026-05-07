<?php

namespace App\Http\Resources\V1;

use App\Models\WorkerCertification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkerCertification
 */
class WorkerCertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certification_type' => $this->whenLoaded('certificationType', fn () => [
                'id' => $this->certificationType->id,
                'code' => $this->certificationType->code,
                'name_en' => $this->certificationType->name_en,
                'name_ar' => $this->certificationType->name_ar,
                'category' => $this->certificationType->category,
            ]),
            'certificate_number' => $this->certificate_number,
            'issuing_body_en' => $this->issuing_body_en,
            'issuing_body_ar' => $this->issuing_body_ar,
            'issue_date' => optional($this->issue_date)->toDateString(),
            'expiry_date' => optional($this->expiry_date)->toDateString(),
            'status' => $this->status, // valid | expiring_soon | expired
            'verified' => (bool) $this->verified,
            'verified_at' => optional($this->verified_at)->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
