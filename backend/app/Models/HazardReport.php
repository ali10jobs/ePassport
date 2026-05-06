<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Anonymity is enforced at the schema level (no submitter_id, no IP, no
 * device_fingerprint). reporter_user_id is set ONLY for authenticated
 * "safety champion" reports where the user explicitly opted to identify.
 */
class HazardReport extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, SoftDeletes;

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_ACTION_ISSUED = 'action_issued';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const CATEGORY_FALL = 'fall';
    public const CATEGORY_ELECTRICAL = 'electrical';
    public const CATEGORY_FIRE = 'fire';
    public const CATEGORY_WORKING_AT_HEIGHTS = 'working_at_heights';
    public const CATEGORY_LIFTING = 'lifting';
    public const CATEGORY_HOUSEKEEPING = 'housekeeping';
    public const CATEGORY_PPE = 'ppe';
    public const CATEGORY_ENVIRONMENTAL = 'environmental';
    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'anonymous_report_id',
        'is_anonymous',
        'reporter_user_id',
        'project_id',
        'site_id',
        'category',
        'severity',
        'description',
        'description_lang',
        'latitude',
        'longitude',
        'status',
        'assigned_to_user_id',
        'assigned_to_organization_id',
        'resolution_summary',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedToOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'assigned_to_organization_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(HazardReportNote::class);
    }

    public function publicNotes(): HasMany
    {
        return $this->hasMany(HazardReportNote::class)->where('note_type', HazardReportNote::TYPE_PUBLIC);
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(HazardReportNote::class)->where('note_type', HazardReportNote::TYPE_INTERNAL);
    }
}
