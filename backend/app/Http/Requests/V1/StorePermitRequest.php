<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
            'issuing_organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'permit_type_id' => ['required', 'uuid', 'exists:permit_types,id'],
            'scope_en' => ['required', 'string', 'max:2000'],
            'scope_ar' => ['nullable', 'string', 'max:2000'],
            'location_description_en' => ['nullable', 'string', 'max:255'],
            'location_description_ar' => ['nullable', 'string', 'max:255'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
