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

export interface ProjectListItem {
  id: string;
  code: string;
  name_en: string;
  name_ar: string;
  status: string;
  city: string | null;
  region: string | null;
}

export interface PermitTypeListItem {
  id: string;
  code: string;
  name_en: string;
  name_ar: string;
  description_en: string | null;
  description_ar: string | null;
  requires_consultant_approval: boolean;
  requires_gas_test: boolean;
  requires_fire_watch: boolean;
  default_validity_hours: number;
}

// Permits — matches App\Models\Permit + PermitResource.
export type PermitStatus =
  | 'draft'
  | 'submitted'
  | 'approved'
  | 'rejected'
  | 'closed'
  | 'expired';

export interface PermitTypeRef {
  id: string;
  code: string;
  name_en: string;
  name_ar: string;
}

export interface PermitListItem {
  id: string;
  permit_number: string;
  project_id: string;
  site_id: string | null;
  issuing_organization_id: string;
  permit_type_id: string;
  permit_type?: PermitTypeRef;
  status: PermitStatus | string;
  scope_en: string;
  scope_ar: string | null;
  location_description_en: string | null;
  location_description_ar: string | null;
  valid_from: string | null;
  valid_until: string | null;
  submitted_at: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  rejection_reason: string | null;
  closed_at: string | null;
  closure_notes: string | null;
  workers_count?: number;
  equipment_count?: number;
  created_at: string | null;
  updated_at: string | null;
}

export interface PermitEvent {
  id: string;
  event_type: string;
  actor_user_id: number | null;
  payload: Record<string, unknown> | null;
  comment: string | null;
  occurred_at: string | null;
}

// Worker / equipment failure structures returned by 422 PERMIT_VALIDATION_FAILED.
export interface PermitWorkerFailure {
  worker_id: string;
  employee_id: string;
  full_name_en: string;
  full_name_ar: string | null;
  role_on_permit: string;
  reasons: ScanReason[];
}

export interface PermitEquipmentFailure {
  equipment_id: string;
  asset_tag: string;
  manufacturer: string | null;
  model: string | null;
  reasons: ScanReason[];
}

export interface PermitProjectFailure {
  code: string;
  details?: Record<string, unknown>;
}

export interface PermitValidationDetails {
  worker_failures: PermitWorkerFailure[];
  equipment_failures: PermitEquipmentFailure[];
  project_failures: PermitProjectFailure[];
}

// Hazard reports — matches App\Models\HazardReport.
export type HazardSeverity = 'low' | 'medium' | 'high' | 'critical';
export type HazardStatus =
  | 'submitted'
  | 'under_review'
  | 'action_issued'
  | 'resolved'
  | 'dismissed';
export type HazardCategory =
  | 'fall'
  | 'electrical'
  | 'fire'
  | 'working_at_heights'
  | 'lifting'
  | 'housekeeping'
  | 'ppe'
  | 'environmental'
  | 'other';

export interface HazardListItem {
  id: string;
  anonymous_report_id: string;
  is_anonymous: boolean;
  category: HazardCategory | string;
  severity: HazardSeverity | string;
  status: HazardStatus | string;
  description: string | null;
  description_lang: string | null;
  project_id: string | null;
  site_id: string | null;
  assigned_to_organization_id: string | null;
  photo_path: string | null;
  created_at: string | null;
  resolved_at: string | null;
}

export interface HazardNote {
  id: string;
  note_type: 'internal' | 'public';
  author_user_id: number | null;
  author_organization_id: string | null;
  body: string;
  body_lang: string | null;
  created_at: string;
}

export interface HazardReportDetail extends HazardListItem {
  latitude: number | null;
  longitude: number | null;
  reporter_user_id: number | null;
  assigned_to_user_id: number | null;
  resolution_summary: string | null;
  notes: HazardNote[];
  updated_at: string | null;
}

export interface AnonymousHazardStatus {
  anonymous_report_id: string;
  status: HazardStatus | string;
  category: HazardCategory | string;
  severity: HazardSeverity | string;
  submitted_at: string;
  resolved_at: string | null;
  resolution_summary: string | null;
  public_updates: Array<{
    body: string;
    body_lang: string | null;
    posted_at: string;
  }>;
}

// Dashboards — matches App\Services\Dashboard\DashboardService payloads.
export interface CertExpiryCounts {
  expired: number;
  expiring_30_days: number;
  expiring_60_days: number;
  expiring_90_days: number;
}

export interface ScanCountsLast24h {
  total_24h: number;
  green_24h: number;
  red_24h: number;
  impersonation_24h: number;
}

export interface ClientDashboard {
  role: 'client';
  project_ids: string[];
  workers: {
    total: number;
    by_organization: Record<string, number>;
  };
  certifications: CertExpiryCounts;
  permits: {
    active_approved: number;
    awaiting_review: number;
    closed_this_week: number;
  };
  hazards: {
    submitted_mtd: number;
    open_critical: number;
    resolved_mtd: number;
  };
  scans: ScanCountsLast24h;
  incident_indicators: {
    red_scans_today: number;
    impersonation_flags_today: number;
    critical_hazards_open: number;
  };
}

export interface MainContractorDashboard {
  role: 'main_contractor';
  organization_id: string;
  subcontractors: string[];
  workers: { mine: number; subs: number };
  equipment: { mine: number; tpi_expired: number };
  certifications: CertExpiryCounts;
  permits: {
    active_approved: number;
    drafts: number;
    awaiting_review: number;
    recently_rejected: number;
  };
  hazards: {
    assigned_to_us: number;
    open_assigned_to_us: number;
  };
}

export interface ConsultantDashboard {
  role: 'consultant';
  organization_id: string;
  project_ids: string[];
  permits: {
    awaiting_review: number;
    approved_today: number;
    rejected_today: number;
    top_awaiting: Array<{
      id: string;
      permit_number: string;
      submitted_at: string | null;
      permit_type_id: string;
    }>;
  };
  hazards: {
    submitted_mtd: number;
    open_critical: number;
    resolved_mtd: number;
  };
  scans: ScanCountsLast24h & { red_scans_today: number };
}

export interface SubcontractorDashboard {
  role: 'subcontractor';
  organization_id: string;
  workers: { total: number; inducted: number; not_inducted: number };
  equipment: { mine: number };
  certifications: CertExpiryCounts;
}

export type DashboardPayload =
  | ClientDashboard
  | MainContractorDashboard
  | ConsultantDashboard
  | SubcontractorDashboard;

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
