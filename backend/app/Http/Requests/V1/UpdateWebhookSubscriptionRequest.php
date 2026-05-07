<?php

namespace App\Http\Requests\V1;

use App\Models\WebhookSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebhookSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:128'],
            'url' => ['sometimes', 'url:https,http', 'max:512'],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['required', 'string', Rule::in([
                WebhookSubscription::EVENT_SCAN_GREEN,
                WebhookSubscription::EVENT_SCAN_RED,
                WebhookSubscription::EVENT_SCAN_IMPERSONATION,
                WebhookSubscription::EVENT_PERMIT_CREATED,
                WebhookSubscription::EVENT_PERMIT_SUBMITTED,
                WebhookSubscription::EVENT_PERMIT_VALIDATED,
                WebhookSubscription::EVENT_PERMIT_APPROVED,
                WebhookSubscription::EVENT_PERMIT_REJECTED,
                WebhookSubscription::EVENT_PERMIT_CLOSED,
                WebhookSubscription::EVENT_HAZARD_SUBMITTED,
                WebhookSubscription::EVENT_HAZARD_STATUS_CHANGED,
                WebhookSubscription::EVENT_HAZARD_RESOLVED,
            ])],
            'headers' => ['sometimes', 'nullable', 'array', 'max:10'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
