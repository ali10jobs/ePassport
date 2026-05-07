<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', Rule::in(self::abilitiesCatalog())],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Catalog of abilities that may be granted to API keys. ERP integrations
     * receive a subset; the full ability list is available to platform admins.
     *
     * @return array<int, string>
     */
    public static function abilitiesCatalog(): array
    {
        return [
            'workers.read',
            'workers.write',
            'equipment.read',
            'equipment.write',
            'permits.read',
            'permits.write',
            'scans.read',
            'scans.create',
            'hazards.read',
            'hazards.write',
            'webhooks.manage',
            'dashboards.read',
        ];
    }
}
