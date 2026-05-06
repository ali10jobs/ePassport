<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AttachWorkerCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'certification_type_id' => ['required', 'uuid', 'exists:certification_types,id'],
            'certificate_number' => ['nullable', 'string', 'max:128'],
            'issuing_body_en' => ['required', 'string', 'max:128'],
            'issuing_body_ar' => ['nullable', 'string', 'max:128'],
            'issue_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date', 'after:issue_date'],
            'verified' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
