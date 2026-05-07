import { Outlet } from 'react-router-dom';

/**
 * AuthLayout — used for unauthenticated routes (login). Centred card
 * on a white canvas; no sidebar, no top bar.
 */
export function AuthLayout() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-background p-6">
      <div className="w-full max-w-sm">
        <Outlet />
      </div>
    </div>
  );
}
