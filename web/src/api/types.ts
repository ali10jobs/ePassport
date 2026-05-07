/**
 * Shared API contract types. The full OpenAPI-generated types land in
 * `api.generated.ts` post-OpenAPI-snapshot pull; this file holds the
 * minimal hand-written shapes we use until that pipeline is wired.
 */

export interface ApiErrorPayload {
  code: string;
  message: string;
  details?: Record<string, unknown> | null;
  request_id?: string | null;
}

export interface ApiErrorBody {
  error: ApiErrorPayload;
}

export class ApiError extends Error {
  readonly code: string;
  readonly status: number;
  readonly details: Record<string, unknown> | null;
  readonly requestId: string | null;

  constructor(status: number, payload: ApiErrorPayload) {
    super(payload.message);
    this.name = 'ApiError';
    this.code = payload.code;
    this.status = status;
    this.details = (payload.details as Record<string, unknown>) ?? null;
    this.requestId = payload.request_id ?? null;
  }
}

export interface UserOrganization {
  id: string;
  name_en: string;
  name_ar: string;
  role: string;
  is_default: boolean;
}

export interface MeUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  locale: string;
  organizations: UserOrganization[];
}

export interface WorkerListItem {
  id: string;
  employer_organization_id: string;
  employer_organization?: {
    id: string;
    name_en: string;
    name_ar: string;
  };
  employee_id: string;
  national_id: string | null;
  iqama_number: string | null;
  passport_number: string | null;
  first_name_en: string;
  last_name_en: string;
  first_name_ar: string | null;
  last_name_ar: string | null;
  full_name_en: string;
  full_name_ar: string;
  nationality: string | null;
  date_of_birth: string | null;
  phone: string | null;
  email: string | null;
  trade: string | null;
  induction_status: 'inducted' | 'not_inducted' | 'expired' | string;
  induction_date: string | null;
  induction_valid_until: string | null;
  photo_path: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string | null;
  updated_at: string | null;
  deleted_at: string | null;
}

export type CertStatus = 'valid' | 'expiring_soon' | 'expired';

export interface PassportCertification {
  id: string;
  type_code: string | null;
  type_name_en: string | null;
  type_name_ar: string | null;
  category: string | null;
  certificate_number: string | null;
  issuing_body_en: string | null;
  issuing_body_ar: string | null;
  issue_date: string | null;
  expiry_date: string | null;
  status: CertStatus;
  verified: boolean;
}

export interface PassportMedical {
  id: string;
  exam_date: string | null;
  valid_until: string | null;
  status: 'fit' | 'fit_with_restrictions' | 'unfit' | string;
  is_currently_fit: boolean;
}

export interface WorkerPassport {
  id: string;
  employee_id: string;
  full_name_en: string;
  full_name_ar: string;
  nationality: string | null;
  trade: string | null;
  employer: {
    id: string | null;
    name_en: string | null;
    name_ar: string | null;
  };
  induction: {
    status: string;
    date: string | null;
    valid_until: string | null;
  };
  medical_fitness: PassportMedical | null;
  certifications: PassportCertification[];
  photo_path: string | null;
}

// Scan verification — matches App\Models\ScanEvent + ScanResult.
export type ScanResultStatus = 'green' | 'red' | 'impersonation_flag';
export type ScanSubjectType = 'worker' | 'equipment' | null;

export type ScanReasonCode =
  | 'CERT_EXPIRED'
  | 'INDUCTION_MISSING'
  | 'MEDICAL_FAIL'
  | 'ORG_NOT_ENGAGED'
  | 'IMPERSONATION_FLAG'
  | 'EQUIPMENT_TPI_EXPIRED'
  | 'OPERATOR_NOT_AUTHORIZED'
  | 'UNKNOWN_QR';

export interface ScanReason {
  code: ScanReasonCode | string;
  details?: Record<string, unknown> | null;
}

export interface ScanResult {
  result: ScanResultStatus;
  subject_type: ScanSubjectType;
  subject_id: string | null;
  token_type: string | null;
  reasons: ScanReason[];
  event_id: string;
  scanned_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number | null;
    to: number | null;
    last_page: number;
    per_page: number;
    total: number;
    path: string;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}
