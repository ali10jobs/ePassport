<?php

namespace App\Http\Requests\V1;

use App\Models\HazardReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAnonymousHazardRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint — no auth required.
        return true;
    }

    public function rules(): array
    {
        return [
            // multipart/form-data: file upload
            'photo' => ['required', 'file', 'image', 'max:10240'], // 10 MB cap
            'category' => ['required', Rule::in([
                HazardReport::CATEGORY_FALL,
                HazardReport::CATEGORY_ELECTRICAL,
                HazardReport::CATEGORY_FIRE,
                HazardReport::CATEGORY_WORKING_AT_HEIGHTS,
                HazardReport::CATEGORY_LIFTING,
                HazardReport::CATEGORY_HOUSEKEEPING,
                HazardReport::CATEGORY_PPE,
                HazardReport::CATEGORY_ENVIRONMENTAL,
                HazardReport::CATEGORY_OTHER,
            ])],
            'severity' => ['required', Rule::in([
                HazardReport::SEVERITY_LOW,
                HazardReport::SEVERITY_MEDIUM,
                HazardReport::SEVERITY_HIGH,
                HazardReport::SEVERITY_CRITICAL,
            ])],
            'description' => ['nullable', 'string', 'max:2000'],
            'description_lang' => ['nullable', 'in:en,ar'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
