import { Languages, Search } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface TopBarProps {
  title: string;
  primaryAction?: {
    label: string;
    onClick: () => void;
  };
}

/**
 * TopBar — 56px tall, breadcrumb/title on start side, search center,
 * language toggle + primary action on end side. Sits above the main
 * canvas (white background).
 */
export function TopBar({ title, primaryAction }: TopBarProps) {
  const { i18n, t } = useTranslation();

  function toggleLang() {
    const next = i18n.language.startsWith('ar') ? 'en' : 'ar';
    i18n.changeLanguage(next);
    document.documentElement.lang = next;
    document.documentElement.dir = next === 'ar' ? 'rtl' : 'ltr';
  }

  return (
    <header className="h-14 shrink-0 flex items-center gap-4 px-4 border-b border-border bg-background">
      <div className="min-w-0 flex-1">
        <h1 className="text-sm font-medium truncate">{title}</h1>
      </div>

      <div className="hidden md:block w-72">
        <div className="relative">
          <Search className="absolute start-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
          <Input
            type="search"
            placeholder={t('shell.search_placeholder', 'Search…')}
            className="ps-8 h-8"
          />
        </div>
      </div>

      <Button
        variant="ghost"
        size="icon"
        onClick={toggleLang}
        title={t('shell.toggle_language', 'Toggle language')}
        aria-label={t('shell.toggle_language', 'Toggle language')}
      >
        <Languages className="size-4" />
      </Button>

      {primaryAction && (
        <Button size="sm" onClick={primaryAction.onClick}>
          {primaryAction.label}
        </Button>
      )}
    </header>
  );
}
