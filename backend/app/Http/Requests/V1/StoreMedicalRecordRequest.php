<?php

namespace App\Http\Requests\V1;

use App\Models\WorkerMedicalRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'exam_date' => ['required', 'date', 'before_or_equal:today'],
            'valid_until' => ['required', 'date', 'after:exam_date'],
            'status' => ['required', Rule::in([
                WorkerMedicalRecord::STATUS_FIT,
                WorkerMedicalRecord::STATUS_FIT_WITH_RESTRICTIONS,
                WorkerMedicalRecord::STATUS_UNFIT,
            ])],
            'examining_clinic_en' => ['nullable', 'string', 'max:128'],
            'examining_clinic_ar' => ['nullable', 'string', 'max:128'],
            'restrictions_en' => ['nullable', 'string', 'max:1000'],
            'restrictions_ar' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
