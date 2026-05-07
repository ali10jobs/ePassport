import { useMutation } from '@tanstack/react-query';

import { endpoints } from '@/api/client';
import type { ScanResult } from '@/api/types';

/**
 * Mutation: POST /api/v1/scans/verify with either { token } (QR scanned)
 * or { employee_id } (manual entry fallback).
 */
export function useScanVerify() {
  return useMutation<
    { data: ScanResult },
    Error,
    { token?: string; employee_id?: string }
  >({
    mutationFn: (input) => endpoints.scans.verify(input),
  });
}
