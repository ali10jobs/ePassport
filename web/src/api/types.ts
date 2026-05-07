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
