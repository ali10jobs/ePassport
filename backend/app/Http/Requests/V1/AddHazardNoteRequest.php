<?php

namespace App\Http\Requests\V1;

use App\Models\HazardReportNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddHazardNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'note_type' => ['required', Rule::in([
                HazardReportNote::TYPE_INTERNAL,
                HazardReportNote::TYPE_PUBLIC,
            ])],
            'body' => ['required', 'string', 'min:1', 'max:4000'],
            'body_lang' => ['nullable', 'in:en,ar'],
            'author_organization_id' => ['nullable', 'uuid', 'exists:organizations,id'],
        ];
    }
}
