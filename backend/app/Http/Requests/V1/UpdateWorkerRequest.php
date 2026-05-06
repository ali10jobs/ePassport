<?php

namespace App\Http\Requests\V1;

use App\Models\Worker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['sometimes', 'string', 'max:64'],
            'national_id' => ['sometimes', 'nullable', 'string', 'max:32'],
            'iqama_number' => ['sometimes', 'nullable', 'string', 'max:32'],
            'passport_number' => ['sometimes', 'nullable', 'string', 'max:32'],
            'first_name_en' => ['sometimes', 'string', 'max:80'],
            'last_name_en' => ['sometimes', 'string', 'max:80'],
            'first_name_ar' => ['sometimes', 'nullable', 'string', 'max:80'],
            'last_name_ar' => ['sometimes', 'nullable', 'string', 'max:80'],
            'nationality' => ['sometimes', 'nullable', 'string', 'size:3'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'trade' => ['sometimes', 'nullable', 'string', 'max:80'],
            'induction_status' => ['sometimes', Rule::in([
                Worker::INDUCTION_NOT_INDUCTED,
                Worker::INDUCTION_INDUCTED,
                Worker::INDUCTION_EXPIRED,
            ])],
            'induction_date' => ['sometimes', 'nullable', 'date'],
            'induction_valid_until' => ['sometimes', 'nullable', 'date'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
