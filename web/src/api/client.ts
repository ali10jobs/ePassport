import { ofetch } from 'ofetch';

import { authStorage } from './auth-storage';
import { ApiError, type ApiErrorBody, type MeUser } from './types';

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
export const api = ofetch.create({
  baseURL: API_BASE,
  credentials: 'include',
  retry: 0,
  async onRequest({ options }) {
    options.headers = new Headers(options.headers ?? {});
    options.headers.set('Accept', 'application/json');
    if (!options.headers.has('Content-Type') && options.body) {
      options.headers.set('Content-Type', 'application/json');
    }
    const token = authStorage.get();
    if (token && !options.headers.has('Authorization')) {
      options.headers.set('Authorization', `Bearer ${token}`);
    }
  },
  async onResponseError({ response }) {
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
  },
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
};
