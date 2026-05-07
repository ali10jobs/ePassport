import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/api/client';

const STALE_5_MIN = 5 * 60_000;

export function useProjects() {
  return useQuery({
    queryKey: ['catalogs', 'projects'],
    queryFn: () => endpoints.catalogs.projects(),
    staleTime: STALE_5_MIN,
  });
}

export function usePermitTypes() {
  return useQuery({
    queryKey: ['catalogs', 'permit-types'],
    queryFn: () => endpoints.catalogs.permitTypes(),
    staleTime: STALE_5_MIN,
  });
}
