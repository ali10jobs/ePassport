import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

import { endpoints } from '@/api/client';

export interface EquipmentListParams {
  page: number;
  perPage: number;
  search?: string;
  type?: string;
  tpiStatus?: 'valid' | 'expired';
}

export function useEquipmentList(params: EquipmentListParams) {
  return useQuery({
    queryKey: ['equipment', 'list', params],
    queryFn: () => endpoints.equipment.list(params),
    placeholderData: keepPreviousData,
  });
}

export function useEquipment(id: string | undefined) {
  return useQuery({
    queryKey: ['equipment', 'detail', id],
    queryFn: () => endpoints.equipment.get(id!),
    enabled: !!id,
  });
}

export function useAttachEquipmentCertification(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (input: Parameters<typeof endpoints.equipment.attachCertification>[1]) =>
      endpoints.equipment.attachCertification(id, input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['equipment', 'detail', id] });
      void queryClient.invalidateQueries({ queryKey: ['equipment', 'list'] });
    },
  });
}
