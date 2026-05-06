<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEquipmentOperatorScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'equipment_token' => ['required', 'string', 'max:255'],
            'worker_token' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id'],
            'client_app' => ['nullable', 'in:web,mobile_ios,mobile_android,api'],
        ];
    }
}
