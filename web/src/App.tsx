import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'sonner';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

import { AppLayout } from '@/components/layout/AppLayout';
import { AuthLayout } from '@/components/layout/AuthLayout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { LoginPage } from '@/features/auth/LoginPage';
import { HazardDetailPage } from '@/features/hazards/HazardDetailPage';
import { HazardsListPage } from '@/features/hazards/HazardsListPage';
import { PublicHazardStatusPage } from '@/features/hazards/PublicHazardStatusPage';
import { NewPermitPage } from '@/features/permits/NewPermitPage';
import { PermitDetailPage } from '@/features/permits/PermitDetailPage';
import { PermitsListPage } from '@/features/permits/PermitsListPage';
import { ScanPage } from '@/features/scans/ScanPage';
import { WorkerDetailPage } from '@/features/workers/WorkerDetailPage';
import { WorkersListPage } from '@/features/workers/WorkersListPage';
import { DashboardPage } from '@/features/dashboard/DashboardPage';
import { EquipmentDetailPage } from '@/features/equipment/EquipmentDetailPage';
import { EquipmentListPage } from '@/features/equipment/EquipmentListPage';
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
          {/* Public — no shell, no auth */}
          <Route path="/hazard-status" element={<PublicHazardStatusPage />} />

          {/* Public — auth card layout */}
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
            <Route path="/equipment" element={<EquipmentListPage />} />
            <Route path="/equipment/:id" element={<EquipmentDetailPage />} />
            <Route path="/scans" element={<ScanPage />} />
            <Route path="/permits" element={<PermitsListPage />} />
            <Route path="/permits/new" element={<NewPermitPage />} />
            <Route path="/permits/:id" element={<PermitDetailPage />} />
            <Route path="/hazards" element={<HazardsListPage />} />
            <Route path="/hazards/:id" element={<HazardDetailPage />} />
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
