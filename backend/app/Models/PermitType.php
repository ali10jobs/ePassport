<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermitType extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'default_validity_hours',
        'requires_consultant_approval',
        'requires_gas_test',
        'requires_fire_watch',
        'is_active',
    ];

    protected $casts = [
        'default_validity_hours' => 'integer',
        'requires_consultant_approval' => 'boolean',
        'requires_gas_test' => 'boolean',
        'requires_fire_watch' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requiredCertifications(): BelongsToMany
    {
        return $this->belongsToMany(CertificationType::class, 'permit_type_required_certifications')
            ->withPivot('applies_to')
            ->withTimestamps();
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }
}
