import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/api/client';
import type { CertRangeParams, DashboardPayload } from '@/api/types';

/**
 * Fetches the dashboard summary for one of the four backend roles.
 * The caller (DashboardPage) picks the role based on the active org.
 *
 * The main-contractor variant accepts optional cert-expiry date ranges so
 * the filtered "expired between" / "expiring between" tiles can refetch.
 */
export function useDashboard(
  role: DashboardPayload['role'] | null,
  certRanges: CertRangeParams = {}
) {
  return useQuery({
    queryKey: ['dashboards', role, certRanges],
    queryFn: async (): Promise<{ data: DashboardPayload }> => {
      switch (role) {
        case 'client':
          return endpoints.dashboards.client();
        case 'main_contractor':
          return endpoints.dashboards.mainContractor(certRanges);
        case 'consultant':
          return endpoints.dashboards.consultant();
        case 'subcontractor':
          return endpoints.dashboards.subcontractor();
        default:
          throw new Error('No dashboard role selected');
      }
    },
    enabled: !!role,
    staleTime: 30_000,
    retry: false,
  });
}
