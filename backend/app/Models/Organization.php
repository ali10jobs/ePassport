<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Organization extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    public const ROLE_CLIENT = 'client';

    public const ROLE_MAIN_CONTRACTOR = 'main_contractor';

    public const ROLE_CONSULTANT = 'consultant';

    public const ROLE_SUBCONTRACTOR = 'subcontractor';

    protected $fillable = [
        'name_en',
        'name_ar',
        'commercial_registration',
        'vat_number',
        'default_role',
        'contact_email',
        'contact_phone',
        'country',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserOrganizationRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organization_roles')
            ->withPivot(['role', 'is_default'])
            ->withTimestamps();
    }

    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_organization_id');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class, 'employer_organization_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'owner_organization_id');
    }

    public function issuedPermits(): HasMany
    {
        return $this->hasMany(Permit::class, 'issuing_organization_id');
    }

    public function webhookSubscriptions(): HasMany
    {
        return $this->hasMany(WebhookSubscription::class, 'owner_organization_id');
    }
}
