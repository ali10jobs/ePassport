<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerMedicalRecord extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_FIT = 'fit';

    public const STATUS_FIT_WITH_RESTRICTIONS = 'fit_with_restrictions';

    public const STATUS_UNFIT = 'unfit';

    protected $fillable = [
        'worker_id',
        'exam_date',
        'valid_until',
        'status',
        'examining_clinic_en',
        'examining_clinic_ar',
        'restrictions_en',
        'restrictions_ar',
        'metadata',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'valid_until' => 'date',
        'metadata' => 'array',
    ];

    public function isFit(): bool
    {
        return $this->status === self::STATUS_FIT
            && $this->valid_until !== null
            && $this->valid_until->gte(now()->startOfDay());
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
