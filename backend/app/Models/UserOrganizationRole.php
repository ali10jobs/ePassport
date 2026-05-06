<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganizationRole extends Model
{
    public const ROLE_PLATFORM_ADMIN = 'platform_admin';
    public const ROLE_HSE_MANAGER = 'hse_manager';
    public const ROLE_SAFETY_ENGINEER = 'safety_engineer';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_CONSULTANT = 'consultant';
    public const ROLE_CLIENT_SAFETY_LEAD = 'client_safety_lead';
    public const ROLE_AUDITOR = 'auditor';
    public const ROLE_WORKER = 'worker';

    protected $fillable = [
        'user_id',
        'organization_id',
        'role',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
