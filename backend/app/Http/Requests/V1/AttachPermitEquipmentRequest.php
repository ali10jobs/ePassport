<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AttachPermitEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'equipment_ids' => ['required_without:tokens', 'nullable', 'array', 'min:1'],
            'equipment_ids.*' => ['required', 'uuid', 'exists:equipment,id'],
            'tokens' => ['required_without:equipment_ids', 'nullable', 'array', 'min:1'],
            'tokens.*' => ['required', 'string', 'max:255'],
        ];
    }
}
