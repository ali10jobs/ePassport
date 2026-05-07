<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HazardReportNote extends Model
{
    use HasUuids, SoftDeletes;

    public const TYPE_INTERNAL = 'internal';

    public const TYPE_PUBLIC = 'public';

    protected $fillable = [
        'hazard_report_id',
        'note_type',
        'author_user_id',
        'author_organization_id',
        'body',
        'body_lang',
    ];

    public function hazardReport(): BelongsTo
    {
        return $this->belongsTo(HazardReport::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function authorOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'author_organization_id');
    }
}
