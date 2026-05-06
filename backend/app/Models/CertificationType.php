<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificationType extends Model
{
    use HasUuids, SoftDeletes;

    public const CATEGORY_SAFETY_TRAINING = 'safety_training';
    public const CATEGORY_TRADE_COMPETENCY = 'trade_competency';
    public const CATEGORY_MEDICAL = 'medical';
    public const CATEGORY_SITE_INDUCTION = 'site_induction';
    public const CATEGORY_TPI = 'tpi';

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'category',
        'default_issuing_body_en',
        'default_issuing_body_ar',
        'typical_validity_months',
        'requires_document_upload',
        'is_active',
    ];

    protected $casts = [
        'requires_document_upload' => 'boolean',
        'is_active' => 'boolean',
        'typical_validity_months' => 'integer',
    ];

    public function workerCertifications(): HasMany
    {
        return $this->hasMany(WorkerCertification::class);
    }

    public function permitTypes(): BelongsToMany
    {
        return $this->belongsToMany(PermitType::class, 'permit_type_required_certifications')
            ->withPivot('applies_to')
            ->withTimestamps();
    }
}
