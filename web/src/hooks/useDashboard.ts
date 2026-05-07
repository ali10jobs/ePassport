import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/api/client';
import type { DashboardPayload } from '@/api/types';

/**
 * Fetches the dashboard summary for one of the four backend roles.
 * The caller (DashboardPage) picks the role based on the active org.
 */
export function useDashboard(role: DashboardPayload['role'] | null) {
  return useQuery({
    queryKey: ['dashboards', role],
    queryFn: async (): Promise<{ data: DashboardPayload }> => {
      switch (role) {
        case 'client':
          return endpoints.dashboards.client();
        case 'main_contractor':
          return endpoints.dashboards.mainContractor();
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
