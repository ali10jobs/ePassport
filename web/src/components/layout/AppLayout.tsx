import { Outlet, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';

/**
 * AppLayout — three-zone shell:
 *   [ dark sidebar | top bar over white main canvas ]
 * Used for every authenticated route.
 */
export function AppLayout() {
  const { t } = useTranslation();
  const location = useLocation();

  // Resolve title from the leading path segment.
  const segment = location.pathname.split('/').filter(Boolean)[0] ?? 'dashboard';
  const title = t(`nav.${segment}`, segment[0].toUpperCase() + segment.slice(1));

  return (
    <div className="flex h-screen overflow-hidden bg-background">
      <Sidebar />
      <div className="flex-1 flex flex-col min-w-0">
        <TopBar title={title} />
        <main className="flex-1 overflow-y-auto">
          <div className="mx-auto max-w-[1280px] px-6 py-6">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
