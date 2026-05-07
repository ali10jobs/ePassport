/**
 * Persists the dev/mobile-style Sanctum bearer token in localStorage.
 *
 * Production prefers cookie sessions (mode=cookie) but cross-origin
 * cookie auth needs CORS + SESSION_DOMAIN coordination. For local dev
 * and as a fallback we use mode=token, which is the same Sanctum PAT
 * the mobile app and ERP integrations use.
 */
const KEY = 'epassport.token';

export const authStorage = {
  get(): string | null {
    try {
      return window.localStorage.getItem(KEY);
    } catch {
      return null;
    }
  },
  set(token: string): void {
    try {
      window.localStorage.setItem(KEY, token);
    } catch {
      // ignore
    }
  },
  clear(): void {
    try {
      window.localStorage.removeItem(KEY);
    } catch {
      // ignore
    }
  },
};
