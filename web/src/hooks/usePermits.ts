import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

import { endpoints } from '@/api/client';

export interface PermitsListParams {
  page: number;
  perPage: number;
  status?: string;
  search?: string;
}

export function usePermits(params: PermitsListParams) {
  return useQuery({
    queryKey: ['permits', 'list', params],
    queryFn: () => endpoints.permits.list(params),
    placeholderData: keepPreviousData,
  });
}

export function usePermit(permitId: string | undefined) {
  return useQuery({
    queryKey: ['permits', 'detail', permitId],
    queryFn: () => endpoints.permits.get(permitId!),
    enabled: !!permitId,
  });
}

export function usePermitEvents(permitId: string | undefined) {
  return useQuery({
    queryKey: ['permits', 'events', permitId],
    queryFn: () => endpoints.permits.events(permitId!),
    enabled: !!permitId,
  });
}

/** Invalidates all the read queries that depend on a permit's state. */
export function useInvalidatePermit() {
  const queryClient = useQueryClient();
  return (permitId: string) => {
    void queryClient.invalidateQueries({ queryKey: ['permits', 'detail', permitId] });
    void queryClient.invalidateQueries({ queryKey: ['permits', 'events', permitId] });
    void queryClient.invalidateQueries({ queryKey: ['permits', 'list'] });
  };
}

export function useCreatePermit() {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: endpoints.permits.create,
    onSuccess: ({ data }) => invalidate(data.id),
  });
}

export function useAttachPermitWorkers(permitId: string) {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: (input: {
      workers?: Array<{ id: string; role_on_permit?: string }>;
      tokens?: string[];
    }) => endpoints.permits.attachWorkers(permitId, input),
    onSuccess: () => invalidate(permitId),
  });
}

export function useSubmitPermit(permitId: string) {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: () => endpoints.permits.submit(permitId),
    onSuccess: () => invalidate(permitId),
  });
}

export function useApprovePermit(permitId: string) {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: (comment?: string) => endpoints.permits.approve(permitId, comment),
    onSuccess: () => invalidate(permitId),
  });
}

export function useRejectPermit(permitId: string) {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: (reason: string) => endpoints.permits.reject(permitId, reason),
    onSuccess: () => invalidate(permitId),
  });
}

export function useClosePermit(permitId: string) {
  const invalidate = useInvalidatePermit();
  return useMutation({
    mutationFn: (closureNotes?: string) => endpoints.permits.close(permitId, closureNotes),
    onSuccess: () => invalidate(permitId),
  });
}
