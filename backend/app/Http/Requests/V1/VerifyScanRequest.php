<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class VerifyScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'token' => ['required_without:employee_id', 'nullable', 'string', 'max:255'],
            'employee_id' => ['required_without:token', 'nullable', 'string', 'max:64'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
            'client_app' => ['nullable', 'in:web,mobile_ios,mobile_android,api'],
        ];
    }
}
