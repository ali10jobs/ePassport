import {
  AlertTriangle,
  ChevronsUpDown,
  HardHat,
  LayoutDashboard,
  LifeBuoy,
  ScanLine,
  Settings,
  ShieldCheck,
  Wrench,
} from 'lucide-react';
import * as React from 'react';
import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/cn';

interface NavItem {
  to: string;
  labelKey: string;
  icon: React.ComponentType<{ className?: string }>;
}

const NAV: NavItem[] = [
  { to: '/dashboard', labelKey: 'nav.dashboard', icon: LayoutDashboard },
  { to: '/workers', labelKey: 'nav.workers', icon: HardHat },
  { to: '/equipment', labelKey: 'nav.equipment', icon: Wrench },
  { to: '/scans', labelKey: 'nav.scans', icon: ScanLine },
  { to: '/permits', labelKey: 'nav.permits', icon: ShieldCheck },
  { to: '/hazards', labelKey: 'nav.hazards', icon: AlertTriangle },
  { to: '/settings', labelKey: 'nav.settings', icon: Settings },
];

/**
 * Sidebar — dark surface, fixed width, matches design-reference.png:
 * org switcher at top, primary nav middle, "help" cell at bottom.
 * RTL-aware via tokens (start/end).
 */
export function Sidebar() {
  const { t } = useTranslation();

  return (
    <aside className="w-60 shrink-0 bg-sidebar text-sidebar-foreground flex flex-col border-e border-sidebar-border">
      {/* Org switcher */}
      <button
        type="button"
        className={cn(
          'flex items-center justify-between gap-2 px-3 h-12',
          'text-sm font-medium border-b border-sidebar-border',
          'hover:bg-sidebar-accent transition-colors duration-100'
        )}
      >
        <div className="flex items-center gap-2 min-w-0">
          <div className="size-6 rounded-sm bg-sidebar-accent grid place-items-center text-[11px] font-semibold">
            eP
          </div>
          <span className="truncate">{t('shell.org_switcher_label', 'Demo MainCo')}</span>
        </div>
        <ChevronsUpDown className="size-3.5 text-sidebar-muted shrink-0" />
      </button>

      {/* Primary nav */}
      <nav className="flex-1 overflow-y-auto px-2 py-3">
        <ul className="space-y-px">
          {NAV.map((item) => (
            <li key={item.to}>
              <NavLink
                to={item.to}
                className={({ isActive }) =>
                  cn(
                    'flex items-center gap-2 px-2.5 h-8 rounded-sm text-[13px]',
                    'transition-colors duration-100',
                    isActive
                      ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                      : 'text-sidebar-foreground/80 hover:text-sidebar-foreground hover:bg-sidebar-accent/60'
                  )
                }
              >
                <item.icon className="size-4 shrink-0" />
                <span className="truncate">{t(item.labelKey, item.labelKey)}</span>
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>

      {/* Help / support cell */}
      <div className="border-t border-sidebar-border p-3">
        <a
          href="mailto:support@epassport.local"
          className={cn(
            'flex items-center gap-2 px-2.5 h-8 rounded-sm text-[13px]',
            'text-sidebar-muted hover:text-sidebar-foreground hover:bg-sidebar-accent/60',
            'transition-colors duration-100'
          )}
        >
          <LifeBuoy className="size-4" />
          <span>{t('shell.support', 'Support')}</span>
        </a>
      </div>
    </aside>
  );
}
