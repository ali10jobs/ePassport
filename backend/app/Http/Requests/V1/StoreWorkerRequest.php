<?php

namespace App\Http\Requests\V1;

use App\Models\Worker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization policies registered later; for now any authenticated user.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'employer_organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'employee_id' => ['required', 'string', 'max:64'],
            'national_id' => ['nullable', 'string', 'max:32'],
            'iqama_number' => ['nullable', 'string', 'max:32'],
            'passport_number' => ['nullable', 'string', 'max:32'],
            'first_name_en' => ['required', 'string', 'max:80'],
            'last_name_en' => ['required', 'string', 'max:80'],
            'first_name_ar' => ['nullable', 'string', 'max:80'],
            'last_name_ar' => ['nullable', 'string', 'max:80'],
            'nationality' => ['nullable', 'string', 'size:3'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'trade' => ['nullable', 'string', 'max:80'],
            'induction_status' => ['nullable', Rule::in([
                Worker::INDUCTION_NOT_INDUCTED,
                Worker::INDUCTION_INDUCTED,
                Worker::INDUCTION_EXPIRED,
            ])],
            'induction_date' => ['nullable', 'date'],
            'induction_valid_until' => ['nullable', 'date', 'after_or_equal:induction_date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'employer_organization_id.exists' => 'The specified employer organization does not exist.',
        ];
    }
}
