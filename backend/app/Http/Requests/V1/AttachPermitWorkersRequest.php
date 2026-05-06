<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachPermitWorkersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'workers' => ['required_without:tokens', 'nullable', 'array', 'min:1'],
            'workers.*.id' => ['required', 'uuid', 'exists:workers,id'],
            'workers.*.role_on_permit' => ['nullable', Rule::in(['worker', 'supervisor', 'gas_tester', 'fire_watch'])],
            'tokens' => ['required_without:workers', 'nullable', 'array', 'min:1'],
            'tokens.*' => ['required', 'string', 'max:255'],
        ];
    }
}
