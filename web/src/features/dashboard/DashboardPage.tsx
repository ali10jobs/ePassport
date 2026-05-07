import { useTranslation } from 'react-i18next';

import { ApiError, type DashboardPayload } from '@/api/types';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/hooks/useAuth';
import { useDashboard } from '@/hooks/useDashboard';

import { ClientView } from './ClientView';
import { ConsultantView } from './ConsultantView';
import { MainContractorView } from './MainContractorView';
import { SubcontractorView } from './SubcontractorView';

/**
 * /dashboard — picks the role-specific view based on the user's active
 * organization and dispatches to one of the four backend dashboard
 * endpoints. Each view consumes its strictly-typed payload.
 */
export function DashboardPage() {
  const { t } = useTranslation();
  const { user } = useAuth();

  // Map the user's first org default_role to a dashboard role. The Spatie
  // role on the org membership ('hse_manager', 'consultant', etc.) is
  // separate from the org TYPE (client / main_contractor / consultant /
  // subcontractor) — which is what the dashboard routes care about.
  // Backend resolves the org behind the scenes; we just need the role tag.
  const role = inferRoleFromUser(user);
  const { data, isLoading, isError, error } = useDashboard(role);

  if (!user) return null;

  if (!role) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>{t('dashboard.no_role.title', 'No dashboard available')}</CardTitle>
          <CardDescription>
            {t(
              'dashboard.no_role.body',
              'Your account is not attached to a client / main contractor / consultant / subcontractor organisation. Contact a platform admin.'
            )}
          </CardDescription>
        </CardHeader>
      </Card>
    );
  }

  if (isLoading) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }

  if (isError) {
    if (error instanceof ApiError && error.code === 'ORG_CONTEXT_MISSING') {
      return (
        <Card>
          <CardHeader>
            <CardTitle>{t('dashboard.no_role.title', 'No dashboard available')}</CardTitle>
            <CardDescription>{error.message}</CardDescription>
          </CardHeader>
        </Card>
      );
    }
    return (
      <div className="text-sm text-destructive">
        {(error as Error)?.message ?? t('dashboard.error', 'Could not load dashboard.')}
      </div>
    );
  }

  const payload = data?.data;
  if (!payload) return null;

  return <Dispatch payload={payload} />;
}

function Dispatch({ payload }: { payload: DashboardPayload }) {
  switch (payload.role) {
    case 'client':
      return <ClientView data={payload} />;
    case 'main_contractor':
      return <MainContractorView data={payload} />;
    case 'consultant':
      return <ConsultantView data={payload} />;
    case 'subcontractor':
      return <SubcontractorView data={payload} />;
  }
}

/**
 * Best-effort role inference from the user's first org membership.
 * The backend ultimately enforces this by reading OrganizationContext
 * server-side; we just pick the matching endpoint.
 *
 * For multi-org users we'd surface an org switcher in the sidebar (already
 * stubbed) and store the active selection. v1.0 picks the first org.
 */
function inferRoleFromUser(
  user: ReturnType<typeof useAuth>['user']
): DashboardPayload['role'] | null {
  if (!user || user.organizations.length === 0) return null;

  // The /me payload doesn't carry the org's default_role today — only the
  // user's role within the org. We leverage the user role as a heuristic:
  //   client_safety_lead         -> client
  //   hse_manager / safety_eng…  -> main_contractor (most common demo path)
  //   consultant                 -> consultant
  // The backend will 403 with ORG_CONTEXT_MISSING if the heuristic is wrong,
  // and we render that gracefully above.
  const role = user.organizations[0].role;
  switch (role) {
    case 'client_safety_lead':
      return 'client';
    case 'consultant':
      return 'consultant';
    case 'hse_manager':
    case 'safety_engineer':
    case 'supervisor':
      return 'main_contractor';
    case 'auditor':
      return 'client';
    case 'worker':
      return 'subcontractor';
    case 'platform_admin':
      return 'main_contractor';
    default:
      return 'main_contractor';
  }
}
