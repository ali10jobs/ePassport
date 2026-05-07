<?php

namespace App\Exceptions\Api;

/**
 * Catalog of stable error codes returned by the API. Once shipped, these
 * strings MUST NOT change — only new ones added.
 *
 * Frontends own the localized message for each code.
 */
final class ErrorCodes
{
    // Auth
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    public const FORBIDDEN = 'FORBIDDEN';

    public const ORG_CONTEXT_MISSING = 'ORG_CONTEXT_MISSING';

    public const ORG_NOT_ACCESSIBLE = 'ORG_NOT_ACCESSIBLE';

    // Generic
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';

    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';

    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';

    public const INTERNAL_ERROR = 'INTERNAL_ERROR';

    public const IDEMPOTENCY_KEY_CONFLICT = 'IDEMPOTENCY_KEY_CONFLICT';

    // Worker / cert
    public const CERT_EXPIRED = 'CERT_EXPIRED';

    public const CERT_MISSING = 'CERT_MISSING';

    public const INDUCTION_MISSING = 'INDUCTION_MISSING';

    public const MEDICAL_FAIL = 'MEDICAL_FAIL';

    public const ORG_NOT_ENGAGED = 'ORG_NOT_ENGAGED';

    public const IMPERSONATION_FLAG = 'IMPERSONATION_FLAG';

    public const UNKNOWN_QR = 'UNKNOWN_QR';

    // Equipment
    public const EQUIPMENT_TPI_EXPIRED = 'EQUIPMENT_TPI_EXPIRED';

    public const OPERATOR_NOT_AUTHORIZED = 'OPERATOR_NOT_AUTHORIZED';

    // Permit
    public const PERMIT_VALIDATION_FAILED = 'PERMIT_VALIDATION_FAILED';

    public const PERMIT_INVALID_TRANSITION = 'PERMIT_INVALID_TRANSITION';

    public const PERMIT_REQUIRES_APPROVAL = 'PERMIT_REQUIRES_APPROVAL';

    // Hazard
    public const HAZARD_PHOTO_MISSING_OR_INVALID = 'HAZARD_PHOTO_MISSING_OR_INVALID';

    public const HAZARD_EXIF_STRIP_FAILED = 'HAZARD_EXIF_STRIP_FAILED';
}
