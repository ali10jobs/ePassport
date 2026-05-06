<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Engagement extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    public const ROLE_MAIN_CONTRACTOR = 'main_contractor';
    public const ROLE_CONSULTANT = 'consultant';
    public const ROLE_SUBCONTRACTOR = 'subcontractor';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'project_id',
        'organization_id',
        'parent_engagement_id',
        'role',
        'scope_en',
        'scope_ar',
        'start_date',
        'end_date',
        'status',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parentEngagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class, 'parent_engagement_id');
    }

    public function childEngagements(): HasMany
    {
        return $this->hasMany(Engagement::class, 'parent_engagement_id');
    }
}
