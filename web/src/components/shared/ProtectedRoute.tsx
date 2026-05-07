import { Navigate, useLocation } from 'react-router-dom';

import { useAuth } from '@/hooks/useAuth';

/**
 * Gate that redirects unauthenticated users to /login. While the initial
 * /me fetch is pending we render nothing so there's no auth flash.
 */
export function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return null;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return <>{children}</>;
}
