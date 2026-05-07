import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

import { endpoints } from '@/api/client';

export interface HazardsListParams {
  page: number;
  perPage: number;
  status?: string;
  severity?: string;
  category?: string;
  search?: string;
}

export function useHazards(params: HazardsListParams) {
  return useQuery({
    queryKey: ['hazards', 'list', params],
    queryFn: () => endpoints.hazards.list(params),
    placeholderData: keepPreviousData,
  });
}

export function useHazard(id: string | undefined) {
  return useQuery({
    queryKey: ['hazards', 'detail', id],
    queryFn: () => endpoints.hazards.get(id!),
    enabled: !!id,
  });
}

function useInvalidateHazard() {
  const queryClient = useQueryClient();
  return (id: string) => {
    void queryClient.invalidateQueries({ queryKey: ['hazards', 'detail', id] });
    void queryClient.invalidateQueries({ queryKey: ['hazards', 'list'] });
  };
}

export function useUpdateHazardStatus(id: string) {
  const invalidate = useInvalidateHazard();
  return useMutation({
    mutationFn: (input: { status: string; resolution_summary?: string }) =>
      endpoints.hazards.updateStatus(id, input),
    onSuccess: () => invalidate(id),
  });
}

export function useAddHazardNote(id: string) {
  const invalidate = useInvalidateHazard();
  return useMutation({
    mutationFn: (input: { note_type: 'internal' | 'public'; body: string; body_lang?: string }) =>
      endpoints.hazards.addNote(id, input),
    onSuccess: () => invalidate(id),
  });
}

export function useAnonymousHazardStatus(anonymousReportId: string | undefined) {
  return useQuery({
    queryKey: ['hazards', 'anonymous', anonymousReportId],
    queryFn: () => endpoints.hazards.anonymousStatus(anonymousReportId!),
    enabled: !!anonymousReportId,
    retry: false,
  });
}
