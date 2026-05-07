<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitEvent extends Model
{
    use HasUuids;

    public const TYPE_CREATED = 'created';

    public const TYPE_SUBMITTED = 'submitted';

    public const TYPE_VALIDATED = 'validated';

    public const TYPE_VALIDATION_FAILED = 'validation_failed';

    public const TYPE_APPROVED = 'approved';

    public const TYPE_REJECTED = 'rejected';

    public const TYPE_CLOSED = 'closed';

    public const TYPE_EXPIRED = 'expired';

    protected $fillable = [
        'permit_id',
        'event_type',
        'actor_user_id',
        'payload',
        'comment',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
