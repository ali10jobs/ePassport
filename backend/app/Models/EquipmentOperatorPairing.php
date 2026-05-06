<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentOperatorPairing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'equipment_id',
        'worker_id',
        'valid_from',
        'valid_until',
        'authorized_by_user_id',
        'authorized_at',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'authorized_at' => 'datetime',
    ];

    public function isCurrentlyValid(): bool
    {
        $today = now()->startOfDay();

        if ($this->valid_from->gt($today)) {
            return false;
        }

        if ($this->valid_until !== null && $this->valid_until->lt($today)) {
            return false;
        }

        return true;
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }
}
