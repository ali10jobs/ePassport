<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'owner_organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'asset_tag' => ['required', 'string', 'max:64'],
            'serial_number' => ['nullable', 'string', 'max:128'],
            'manufacturer' => ['nullable', 'string', 'max:128'],
            'model' => ['nullable', 'string', 'max:128'],
            'type' => ['required', 'string', 'max:64'],
            'category' => ['nullable', 'string', 'max:128'],
            'manufacture_date' => ['nullable', 'date', 'before_or_equal:today'],
            'safe_working_load_kg' => ['nullable', 'numeric', 'min:0'],
            'specs' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
