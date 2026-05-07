import { useQuery, useQueryClient } from '@tanstack/react-query';

import { endpoints } from '@/api/client';
import { ApiError, type MeUser } from '@/api/types';

const ME_KEY = ['me'] as const;

/**
 * Auth state lives entirely in TanStack Query — no Zustand layer.
 * `data` is the current user (or undefined when unauthenticated).
 * `isLoading` covers the initial bootstrap fetch only.
 */
export function useAuth() {
  const queryClient = useQueryClient();

  const query = useQuery({
    queryKey: ME_KEY,
    queryFn: async (): Promise<MeUser | null> => {
      try {
        const response = await endpoints.me();
        return response.data;
      } catch (err) {
        if (err instanceof ApiError && err.status === 401) {
          return null;
        }
        throw err;
      }
    },
    staleTime: 60_000,
    retry: false,
  });

  function setUser(user: MeUser | null) {
    queryClient.setQueryData<MeUser | null>(ME_KEY, user);
  }

  function clear() {
    queryClient.setQueryData<MeUser | null>(ME_KEY, null);
  }

  return {
    user: query.data ?? null,
    isAuthenticated: !!query.data,
    isLoading: query.isLoading,
    isError: query.isError,
    setUser,
    clear,
  };
}

export { ME_KEY };
