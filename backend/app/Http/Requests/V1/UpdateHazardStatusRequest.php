<?php

namespace App\Http\Requests\V1;

use App\Models\HazardReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHazardStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                HazardReport::STATUS_UNDER_REVIEW,
                HazardReport::STATUS_ACTION_ISSUED,
                HazardReport::STATUS_RESOLVED,
                HazardReport::STATUS_DISMISSED,
            ])],
            'resolution_summary' => ['nullable', 'string', 'max:2000'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_to_organization_id' => ['nullable', 'uuid', 'exists:organizations,id'],
        ];
    }
}
