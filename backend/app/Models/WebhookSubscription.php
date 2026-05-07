<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebhookSubscription extends Model
{
    use HasUuids, SoftDeletes;

    public const EVENT_SCAN_GREEN = 'scan.green';

    public const EVENT_SCAN_RED = 'scan.red';

    public const EVENT_SCAN_IMPERSONATION = 'scan.impersonation_flag';

    public const EVENT_PERMIT_CREATED = 'permit.created';

    public const EVENT_PERMIT_SUBMITTED = 'permit.submitted';

    public const EVENT_PERMIT_VALIDATED = 'permit.validated';

    public const EVENT_PERMIT_APPROVED = 'permit.approved';

    public const EVENT_PERMIT_REJECTED = 'permit.rejected';

    public const EVENT_PERMIT_CLOSED = 'permit.closed';

    public const EVENT_HAZARD_SUBMITTED = 'hazard_report.submitted';

    public const EVENT_HAZARD_STATUS_CHANGED = 'hazard_report.status_changed';

    public const EVENT_HAZARD_RESOLVED = 'hazard_report.resolved';

    protected $fillable = [
        'owner_organization_id',
        'label',
        'url',
        'secret',
        'events',
        'active',
        'headers',
        'failure_count',
        'disabled_at',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'active' => 'boolean',
        'failure_count' => 'integer',
        'disabled_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    public function ownerOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'owner_organization_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'subscription_id');
    }
}
