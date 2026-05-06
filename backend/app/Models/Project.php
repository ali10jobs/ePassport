<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    public const STATUS_PLANNING = 'planning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'client_organization_id',
        'code',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'status',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'city',
        'region',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function clientOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'client_organization_id');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }

    public function hazardReports(): HasMany
    {
        return $this->hasMany(HazardReport::class);
    }
}
