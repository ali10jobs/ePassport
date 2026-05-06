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

class Equipment extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'owner_organization_id',
        'asset_tag',
        'serial_number',
        'manufacturer',
        'model',
        'type',
        'category',
        'manufacture_date',
        'safe_working_load_kg',
        'qr_token',
        'specs',
        'metadata',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'safe_working_load_kg' => 'decimal:2',
        'specs' => 'array',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'qr_token',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function ownerOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'owner_organization_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(EquipmentCertification::class);
    }

    public function latestCertification(): HasOne
    {
        // latestOfMany() adds MAX(id) as tiebreaker; Postgres can't MAX a UUID.
        return $this->hasOne(EquipmentCertification::class)
            ->orderByDesc('inspection_date')
            ->orderByDesc('created_at');
    }

    public function operatorPairings(): HasMany
    {
        return $this->hasMany(EquipmentOperatorPairing::class);
    }

    public function authorizedOperators(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class, 'equipment_operator_pairings')
            ->withPivot(['valid_from', 'valid_until', 'authorized_by_user_id', 'authorized_at'])
            ->withTimestamps();
    }

    public function permits(): BelongsToMany
    {
        return $this->belongsToMany(Permit::class, 'permit_equipment')->withTimestamps();
    }
}
