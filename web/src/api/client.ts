import { ofetch } from 'ofetch';

import { authStorage } from './auth-storage';
import {
  ApiError,
  type AnonymousHazardStatus,
  type ApiErrorBody,
  type ClientDashboard,
  type ConsultantDashboard,
  type EquipmentListItem,
  type HazardListItem,
  type HazardNote,
  type HazardReportDetail,
  type MainContractorDashboard,
  type MeUser,
  type PaginatedResponse,
  type PermitEvent,
  type PermitListItem,
  type PermitTypeListItem,
  type ProjectListItem,
  type ScanResult,
  type SubcontractorDashboard,
  type WorkerListItem,
  type WorkerPassport,
} from './types';

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000';

/**
 * Base ofetch instance.
 *
 * - Sends a Sanctum bearer token from localStorage when present
 *   (mode=token). Same auth path the mobile app and ERP integrations
 *   use; works cross-origin without CORS-credentials coordination.
 * - Falls back to credentials:include so future cookie-mode SPA auth
 *   (mode=cookie) lights up the moment SESSION_DOMAIN + CORS align on
 *   the deployed pair.
 * - Surfaces the platform's stable error codes via the ApiError class.
 */
function jsonHeaders({ options }: { options: { headers?: HeadersInit; body?: unknown } }) {
  options.headers = new Headers(options.headers ?? {});
  options.headers.set('Accept', 'application/json');
  if (!options.headers.has('Content-Type') && options.body) {
    options.headers.set('Content-Type', 'application/json');
  }
}

function unwrapApiError({ response }: { response: { _data?: unknown; status: number; statusText: string; headers: Headers } }) {
  const body = response._data as ApiErrorBody | undefined;
  if (body && typeof body === 'object' && 'error' in body) {
    throw new ApiError(response.status, body.error);
  }
  throw new ApiError(response.status, {
    code: 'UNKNOWN_ERROR',
    message: response.statusText || 'Unknown error',
    details: null,
    request_id: response.headers.get('x-request-id'),
  });
}

export const api = ofetch.create({
  baseURL: API_BASE,
  credentials: 'include',
  retry: 0,
  async onRequest({ options }) {
    jsonHeaders({ options });
    const token = authStorage.get();
    if (token && !(options.headers as Headers).has('Authorization')) {
      (options.headers as Headers).set('Authorization', `Bearer ${token}`);
    }
  },
  onResponseError: unwrapApiError,
});

/**
 * Companion ofetch instance used for endpoints that must be reachable
 * by logged-out browsers (the public anonymous-hazard status check).
 * No token injection, no credentials cookie attached.
 */
const publicApi = ofetch.create({
  baseURL: API_BASE,
  credentials: 'omit',
  retry: 0,
  async onRequest({ options }) {
    jsonHeaders({ options });
  },
  onResponseError: unwrapApiError,
});

/**
 * Endpoints called early in the SPA lifecycle. Feature-specific calls
 * land in their respective `features/*` modules.
 */
export const endpoints = {
  async login(input: {
    email: string;
    password: string;
    mode?: 'cookie' | 'token';
    device_name?: string;
  }) {
    return api<{ data: { token_type: string; access_token?: string; user: MeUser } }>(
      '/api/v1/auth/login',
      {
        method: 'POST',
        body: { mode: 'token', device_name: 'web', ...input },
      }
    );
  },
  async logout() {
    return api('/api/v1/auth/logout', { method: 'POST' });
  },
  async me() {
    return api<{ data: MeUser }>('/api/v1/me');
  },
  async health() {
    return api<{
      status: string;
      service: string;
      version: string;
      checks: Record<string, { ok: boolean }>;
    }>('/api/v1/health');
  },

  workers: {
    async list(params: {
      page?: number;
      perPage?: number;
      search?: string;
      inductionStatus?: string;
      certStatus?: 'expired' | 'valid';
      employerOrganizationId?: string;
      include?: string;
    }): Promise<PaginatedResponse<WorkerListItem>> {
      const query: Record<string, string | number> = {};
      if (params.page) query.page = params.page;
      if (params.perPage) query.per_page = params.perPage;
      if (params.search) query['filter[search]'] = params.search;
      if (params.inductionStatus) query['filter[induction_status]'] = params.inductionStatus;
      if (params.certStatus) query['filter[cert_status]'] = params.certStatus;
      if (params.employerOrganizationId)
        query['filter[employer_organization_id]'] = params.employerOrganizationId;
      query.include = params.include ?? 'employerOrganization';

      return api<PaginatedResponse<WorkerListItem>>('/api/v1/workers', {
        method: 'GET',
        query,
      });
    },
    async passport(workerId: string): Promise<{ data: WorkerPassport }> {
      return api<{ data: WorkerPassport }>(`/api/v1/workers/${workerId}/passport`);
    },
  },

  scans: {
    async verify(input: {
      token?: string;
      employee_id?: string;
      project_id?: string;
      site_id?: string;
      client_app?: 'web' | 'mobile_ios' | 'mobile_android' | 'api';
    }): Promise<{ data: ScanResult }> {
      return api<{ data: ScanResult }>('/api/v1/scans/verify', {
        method: 'POST',
        body: { client_app: 'web', ...input },
      });
    },
    async verifyPair(input: {
      helmet_token: string;
      coverall_token: string;
      project_id?: string;
      site_id?: string;
    }): Promise<{ data: ScanResult }> {
      return api<{ data: ScanResult }>('/api/v1/scans/verify-pair', {
        method: 'POST',
        body: { client_app: 'web', ...input },
      });
    },
  },

  permits: {
    async list(params: {
      page?: number;
      perPage?: number;
      status?: string;
      search?: string;
      include?: string;
    }): Promise<PaginatedResponse<PermitListItem>> {
      const query: Record<string, string | number> = {};
      if (params.page) query.page = params.page;
      if (params.perPage) query.per_page = params.perPage;
      if (params.status) query['filter[status]'] = params.status;
      if (params.search) query['filter[search]'] = params.search;
      query.include = params.include ?? 'permitType';

      return api<PaginatedResponse<PermitListItem>>('/api/v1/permits', {
        method: 'GET',
        query,
      });
    },
    async get(permitId: string): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>(`/api/v1/permits/${permitId}`, {
        method: 'GET',
        query: { include: 'permitType' },
      });
    },
    async create(input: {
      project_id: string;
      issuing_organization_id: string;
      permit_type_id: string;
      scope_en: string;
      scope_ar?: string;
      site_id?: string;
      location_description_en?: string;
      location_description_ar?: string;
      valid_from?: string;
      valid_until?: string;
    }): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>('/api/v1/permits', {
        method: 'POST',
        body: input,
      });
    },
    async attachWorkers(
      permitId: string,
      input: {
        workers?: Array<{ id: string; role_on_permit?: string }>;
        tokens?: string[];
      }
    ): Promise<{ data: { attached: number; already_attached: number; unknown_tokens: string[] } }> {
      return api(`/api/v1/permits/${permitId}/workers`, {
        method: 'POST',
        body: input,
      });
    },
    async submit(permitId: string): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>(`/api/v1/permits/${permitId}/submit`, {
        method: 'POST',
      });
    },
    async approve(permitId: string, comment?: string): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>(`/api/v1/permits/${permitId}/approve`, {
        method: 'POST',
        body: comment ? { comment } : {},
      });
    },
    async reject(permitId: string, reason: string): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>(`/api/v1/permits/${permitId}/reject`, {
        method: 'POST',
        body: { reason },
      });
    },
    async close(permitId: string, closureNotes?: string): Promise<{ data: PermitListItem }> {
      return api<{ data: PermitListItem }>(`/api/v1/permits/${permitId}/close`, {
        method: 'POST',
        body: closureNotes ? { closure_notes: closureNotes } : {},
      });
    },
    async events(permitId: string): Promise<{ data: PermitEvent[] }> {
      return api<{ data: PermitEvent[] }>(`/api/v1/permits/${permitId}/events`);
    },
  },

  equipment: {
    async list(params: {
      page?: number;
      perPage?: number;
      search?: string;
      type?: string;
      tpiStatus?: 'valid' | 'expired';
      ownerOrganizationId?: string;
      include?: string;
    }): Promise<PaginatedResponse<EquipmentListItem>> {
      const query: Record<string, string | number> = {};
      if (params.page) query.page = params.page;
      if (params.perPage) query.per_page = params.perPage;
      if (params.search) query['filter[search]'] = params.search;
      if (params.type) query['filter[type]'] = params.type;
      if (params.tpiStatus) query['filter[tpi_status]'] = params.tpiStatus;
      if (params.ownerOrganizationId)
        query['filter[owner_organization_id]'] = params.ownerOrganizationId;
      query.include = params.include ?? 'ownerOrganization,latestCertification';

      return api<PaginatedResponse<EquipmentListItem>>('/api/v1/equipment', {
        method: 'GET',
        query,
      });
    },
    async get(id: string): Promise<{ data: EquipmentListItem }> {
      return api<{ data: EquipmentListItem }>(`/api/v1/equipment/${id}`, {
        query: { include: 'ownerOrganization,latestCertification' },
      });
    },
    async attachCertification(
      id: string,
      input: {
        tpi_body_en: string;
        tpi_body_ar?: string;
        inspection_date: string;
        expiry_date: string;
        result: 'pass' | 'pass_with_conditions' | 'fail';
        certificate_number?: string;
        notes?: string;
      }
    ): Promise<{ data: { id: string; result: string; is_valid: boolean } }> {
      return api(`/api/v1/equipment/${id}/certifications`, {
        method: 'POST',
        body: input,
      });
    },
  },

  catalogs: {
    async projects(): Promise<{ data: ProjectListItem[] }> {
      return api<{ data: ProjectListItem[] }>('/api/v1/projects');
    },
    async permitTypes(): Promise<{ data: PermitTypeListItem[] }> {
      return api<{ data: PermitTypeListItem[] }>('/api/v1/permit-types');
    },
  },

  hazards: {
    async list(params: {
      page?: number;
      perPage?: number;
      status?: string;
      severity?: string;
      category?: string;
      search?: string;
    }): Promise<PaginatedResponse<HazardListItem>> {
      const query: Record<string, string | number> = {};
      if (params.page) query.page = params.page;
      if (params.perPage) query.per_page = params.perPage;
      if (params.status) query['filter[status]'] = params.status;
      if (params.severity) query['filter[severity]'] = params.severity;
      if (params.category) query['filter[category]'] = params.category;
      if (params.search) query['filter[search]'] = params.search;
      return api<PaginatedResponse<HazardListItem>>('/api/v1/hazard-reports', {
        method: 'GET',
        query,
      });
    },
    async get(id: string): Promise<{ data: HazardReportDetail }> {
      return api<{ data: HazardReportDetail }>(`/api/v1/hazard-reports/${id}`);
    },
    async updateStatus(
      id: string,
      input: {
        status: string;
        resolution_summary?: string;
      }
    ): Promise<{ data: HazardReportDetail }> {
      return api<{ data: HazardReportDetail }>(`/api/v1/hazard-reports/${id}`, {
        method: 'PATCH',
        body: input,
      });
    },
    async addNote(
      id: string,
      input: {
        note_type: 'internal' | 'public';
        body: string;
        body_lang?: string;
      }
    ): Promise<{ data: HazardNote }> {
      return api<{ data: HazardNote }>(`/api/v1/hazard-reports/${id}/notes`, {
        method: 'POST',
        body: input,
      });
    },
    /**
     * Public anonymous status check. Uses publicApi so no bearer token is
     * sent and no session cookie is attached — the call is callable by a
     * fully logged-out browser.
     */
    async anonymousStatus(anonymousReportId: string): Promise<{ data: AnonymousHazardStatus }> {
      return publicApi<{ data: AnonymousHazardStatus }>(
        `/api/v1/hazard-reports/anonymous/${anonymousReportId}`
      );
    },
  },

  dashboards: {
    async client(): Promise<{ data: ClientDashboard }> {
      return api<{ data: ClientDashboard }>('/api/v1/dashboards/client/summary');
    },
    async mainContractor(): Promise<{ data: MainContractorDashboard }> {
      return api<{ data: MainContractorDashboard }>('/api/v1/dashboards/main-contractor/summary');
    },
    async consultant(): Promise<{ data: ConsultantDashboard }> {
      return api<{ data: ConsultantDashboard }>('/api/v1/dashboards/consultant/summary');
    },
    async subcontractor(): Promise<{ data: SubcontractorDashboard }> {
      return api<{ data: SubcontractorDashboard }>('/api/v1/dashboards/subcontractor/summary');
    },
  },
};
