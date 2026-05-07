import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'sonner';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

import { AppLayout } from '@/components/layout/AppLayout';
import { AuthLayout } from '@/components/layout/AuthLayout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { LoginPage } from '@/features/auth/LoginPage';
import { ScanPage } from '@/features/scans/ScanPage';
import { WorkerDetailPage } from '@/features/workers/WorkerDetailPage';
import { WorkersListPage } from '@/features/workers/WorkersListPage';
import { DashboardPage } from '@/pages/DashboardPage';
import { PlaceholderPage } from '@/pages/PlaceholderPage';

import '@/i18n';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      refetchOnWindowFocus: false,
      retry: (count, error) => {
        // Don't retry auth errors — let the route gate redirect.
        if (
          error instanceof Error &&
          'status' in error &&
          (error as { status: number }).status === 401
        ) {
          return false;
        }
        return count < 1;
      },
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          {/* Public */}
          <Route element={<AuthLayout />}>
            <Route path="/login" element={<LoginPage />} />
          </Route>

          {/* Authenticated */}
          <Route
            element={
              <ProtectedRoute>
                <AppLayout />
              </ProtectedRoute>
            }
          >
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/workers" element={<WorkersListPage />} />
            <Route path="/workers/:id" element={<WorkerDetailPage />} />
            <Route
              path="/equipment"
              element={<PlaceholderPage titleKey="nav.equipment" fallback="Equipment" />}
            />
            <Route path="/scans" element={<ScanPage />} />
            <Route
              path="/permits"
              element={<PlaceholderPage titleKey="nav.permits" fallback="Permits" />}
            />
            <Route
              path="/hazards"
              element={<PlaceholderPage titleKey="nav.hazards" fallback="Hazards" />}
            />
            <Route
              path="/settings"
              element={<PlaceholderPage titleKey="nav.settings" fallback="Settings" />}
            />
          </Route>

          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
      <Toaster richColors closeButton position="top-right" />
    </QueryClientProvider>
  );
}
