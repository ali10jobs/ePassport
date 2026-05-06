<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'equipment' => ['required', 'array', 'min:1', 'max:500'],
            'equipment.*' => ['required', 'array'],
        ];
    }
}
