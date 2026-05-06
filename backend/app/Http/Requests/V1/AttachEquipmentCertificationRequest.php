<?php

namespace App\Http\Requests\V1;

use App\Models\EquipmentCertification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachEquipmentCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'certificate_number' => ['nullable', 'string', 'max:128'],
            'inspection_type' => ['required', Rule::in(['periodic', 'major', 'post_repair', 'initial'])],
            'tpi_body_en' => ['required', 'string', 'max:128'],
            'tpi_body_ar' => ['nullable', 'string', 'max:128'],
            'inspector_name' => ['nullable', 'string', 'max:128'],
            'inspection_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['required', 'date', 'after:inspection_date'],
            'result' => ['required', Rule::in([
                EquipmentCertification::RESULT_PASS,
                EquipmentCertification::RESULT_PASS_WITH_CONDITIONS,
                EquipmentCertification::RESULT_FAIL,
            ])],
            'conditions_en' => ['nullable', 'string', 'max:1000'],
            'conditions_ar' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
