<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Worker extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    public const INDUCTION_NOT_INDUCTED = 'not_inducted';
    public const INDUCTION_INDUCTED = 'inducted';
    public const INDUCTION_EXPIRED = 'expired';

    protected $fillable = [
        'employer_organization_id',
        'employee_id',
        'national_id',
        'iqama_number',
        'passport_number',
        'first_name_en',
        'last_name_en',
        'first_name_ar',
        'last_name_ar',
        'nationality',
        'date_of_birth',
        'phone',
        'email',
        'trade',
        'induction_status',
        'induction_date',
        'induction_valid_until',
        'helmet_qr_token',
        'coverall_qr_token',
        'photo_path',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'induction_date' => 'date',
        'induction_valid_until' => 'date',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'helmet_qr_token',
        'coverall_qr_token',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function getFullNameEnAttribute(): string
    {
        return trim("{$this->first_name_en} {$this->last_name_en}");
    }

    public function getFullNameArAttribute(): string
    {
        return trim("{$this->first_name_ar} {$this->last_name_ar}");
    }

    public function employerOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'employer_organization_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(WorkerCertification::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(WorkerMedicalRecord::class);
    }

    public function latestMedicalRecord(): HasOne
    {
        // latestOfMany() adds MAX(id) as tiebreaker; Postgres can't MAX a UUID,
        // so we order by exam_date desc, created_at desc and take 1.
        return $this->hasOne(WorkerMedicalRecord::class)
            ->orderByDesc('exam_date')
            ->orderByDesc('created_at');
    }

    public function operatorPairings(): HasMany
    {
        return $this->hasMany(EquipmentOperatorPairing::class);
    }

    public function authorizedEquipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'equipment_operator_pairings')
            ->withPivot(['valid_from', 'valid_until', 'authorized_by_user_id', 'authorized_at'])
            ->withTimestamps();
    }

    public function permits(): BelongsToMany
    {
        return $this->belongsToMany(Permit::class, 'permit_workers')
            ->withPivot('role_on_permit')
            ->withTimestamps();
    }
}
