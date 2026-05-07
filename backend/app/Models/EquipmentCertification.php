<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EquipmentCertification extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    public const RESULT_PASS = 'pass';

    public const RESULT_PASS_WITH_CONDITIONS = 'pass_with_conditions';

    public const RESULT_FAIL = 'fail';

    protected $fillable = [
        'equipment_id',
        'certificate_number',
        'inspection_type',
        'tpi_body_en',
        'tpi_body_ar',
        'inspector_name',
        'inspection_date',
        'expiry_date',
        'result',
        'conditions_en',
        'conditions_ar',
        'metadata',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'expiry_date' => 'date',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function isValid(): bool
    {
        return in_array($this->result, [self::RESULT_PASS, self::RESULT_PASS_WITH_CONDITIONS], true)
            && $this->expiry_date->gte(now()->startOfDay());
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
