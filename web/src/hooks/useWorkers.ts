import { keepPreviousData, useQuery } from '@tanstack/react-query';

import { endpoints } from '@/api/client';

export interface WorkersListParams {
  page: number;
  perPage: number;
  search?: string;
  inductionStatus?: string;
  certStatus?: 'expired' | 'valid';
}

export function useWorkers(params: WorkersListParams) {
  return useQuery({
    queryKey: ['workers', 'list', params],
    queryFn: () => endpoints.workers.list(params),
    placeholderData: keepPreviousData,
  });
}

export function useWorkerPassport(workerId: string | undefined) {
  return useQuery({
    queryKey: ['workers', 'passport', workerId],
    queryFn: () => endpoints.workers.passport(workerId!),
    enabled: !!workerId,
  });
}

/**
 * Fetches the worker's helmet QR PNG (authenticated, so we must pull it as a
 * Blob and turn it into an objectURL the <img> can render).
 */
export function useWorkerHelmetQr(workerId: string | undefined) {
  return useQuery({
    queryKey: ['workers', 'qr', 'helmet', workerId],
    queryFn: () => endpoints.workers.helmetQrPng(workerId!),
    enabled: !!workerId,
    staleTime: 60_000,
  });
}
