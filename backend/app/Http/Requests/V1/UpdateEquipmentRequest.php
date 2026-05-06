<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'asset_tag' => ['sometimes', 'string', 'max:64'],
            'serial_number' => ['sometimes', 'nullable', 'string', 'max:128'],
            'manufacturer' => ['sometimes', 'nullable', 'string', 'max:128'],
            'model' => ['sometimes', 'nullable', 'string', 'max:128'],
            'type' => ['sometimes', 'string', 'max:64'],
            'category' => ['sometimes', 'nullable', 'string', 'max:128'],
            'manufacture_date' => ['sometimes', 'nullable', 'date'],
            'safe_working_load_kg' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'specs' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
