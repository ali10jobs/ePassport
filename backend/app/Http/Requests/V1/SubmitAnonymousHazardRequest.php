<?php

namespace App\Http\Requests\V1;

use App\Models\HazardReport;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
            // multipart/form-data: accepts either a single `photo` (legacy)
            // or a `photos[]` array (up to 5 files). At least one must be
            // present — enforced via withValidator() below.
            'photo' => ['nullable', 'file', 'image', 'max:10240'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['file', 'image', 'max:10240'],
            'category' => ['required', Rule::in([
                HazardReport::CATEGORY_FALL,
                HazardReport::CATEGORY_ELECTRICAL,
                HazardReport::CATEGORY_FIRE,
                HazardReport::CATEGORY_WORKING_AT_HEIGHTS,
                HazardReport::CATEGORY_LIFTING,
                HazardReport::CATEGORY_HOUSEKEEPING,
                HazardReport::CATEGORY_PPE,
                HazardReport::CATEGORY_ENVIRONMENTAL,
                HazardReport::CATEGORY_TOXIC,
                HazardReport::CATEGORY_IMPACT,
                HazardReport::CATEGORY_OTHER,
            ])],
            'severity' => ['required', Rule::in([
                HazardReport::SEVERITY_LOW,
                HazardReport::SEVERITY_MEDIUM,
                HazardReport::SEVERITY_HIGH,
                HazardReport::SEVERITY_CRITICAL,
            ])],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
            'description_lang' => ['nullable', 'in:en,ar'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // When true AND the request carries a valid Sanctum token, the
            // report is attributed to the authenticated user instead of being
            // recorded as anonymous. Submitters still default to anonymous so
            // the no-PII guarantee of the public endpoint is preserved.
            'make_public_identity' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (count($this->uploadedPhotos()) === 0) {
                $v->errors()->add('photos', 'At least one photo is required.');
            }
        });
    }

    /**
     * Returns the photos to process as a flat list of UploadedFile, merging
     * the legacy single-`photo` field with the new `photos[]` array.
     *
     * @return list<UploadedFile>
     */
    public function uploadedPhotos(): array
    {
        $files = [];
        if ($this->hasFile('photo')) {
            $single = $this->file('photo');
            if ($single instanceof UploadedFile) {
                $files[] = $single;
            }
        }
        if ($this->hasFile('photos')) {
            foreach ((array) $this->file('photos') as $f) {
                if ($f instanceof UploadedFile) {
                    $files[] = $f;
                }
            }
        }

        return $files;
    }
}
