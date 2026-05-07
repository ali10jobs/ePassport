import { useTranslation } from 'react-i18next';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface PlaceholderPageProps {
  titleKey: string;
  fallback: string;
}

/**
 * Stub page used until the real feature lands. Surfaces the route name
 * so navigation works during early demo runs.
 */
export function PlaceholderPage({ titleKey, fallback }: PlaceholderPageProps) {
  const { t } = useTranslation();
  return (
    <div className="space-y-4">
      <div>
        <h2 className="text-lg font-medium">{t(titleKey, fallback)}</h2>
        <p className="text-sm text-muted-foreground">
          {t('placeholder.body', 'This page is part of the v1.0 sprint and is not built yet.')}
        </p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>{t('placeholder.coming_soon', 'Coming soon')}</CardTitle>
          <CardDescription>
            {t('placeholder.description', 'Wired in the next phase. Open the OpenAPI docs at /api/v1/docs to inspect what will land here.')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <code className="text-xs text-muted-foreground">{titleKey}</code>
        </CardContent>
      </Card>
    </div>
  );
}
