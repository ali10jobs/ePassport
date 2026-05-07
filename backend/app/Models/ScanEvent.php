<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScanEvent extends Model
{
    use HasUuids;

    public const RESULT_GREEN = 'green';

    public const RESULT_RED = 'red';

    public const RESULT_IMPERSONATION_FLAG = 'impersonation_flag';

    public const SUBJECT_WORKER = 'worker';

    public const SUBJECT_EQUIPMENT = 'equipment';

    public const TOKEN_HELMET = 'helmet';

    public const TOKEN_COVERALL = 'coverall';

    public const TOKEN_EQUIPMENT = 'equipment';

    public const TOKEN_MANUAL = 'manual';

    // Reason codes used in the reasons[] payload
    public const REASON_CERT_EXPIRED = 'CERT_EXPIRED';

    public const REASON_INDUCTION_MISSING = 'INDUCTION_MISSING';

    public const REASON_MEDICAL_FAIL = 'MEDICAL_FAIL';

    public const REASON_ORG_NOT_ENGAGED = 'ORG_NOT_ENGAGED';

    public const REASON_IMPERSONATION_FLAG = 'IMPERSONATION_FLAG';

    public const REASON_EQUIPMENT_TPI_EXPIRED = 'EQUIPMENT_TPI_EXPIRED';

    public const REASON_OPERATOR_NOT_AUTHORIZED = 'OPERATOR_NOT_AUTHORIZED';

    public const REASON_UNKNOWN_QR = 'UNKNOWN_QR';

    protected $fillable = [
        'scanner_user_id',
        'site_id',
        'subject_type',
        'subject_id',
        'scan_token_type',
        'scan_token',
        'result',
        'reasons',
        'paired_scan_data',
        'is_manual_entry',
        'is_offline_originated',
        'client_app',
        'idempotency_key',
        'scanned_at',
    ];

    protected $casts = [
        'reasons' => 'array',
        'paired_scan_data' => 'array',
        'is_manual_entry' => 'boolean',
        'is_offline_originated' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    protected $hidden = [
        'scan_token',
    ];

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanner_user_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
