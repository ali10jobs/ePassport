import { Search, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useSearchParams } from 'react-router-dom';

import { ApiError } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { HazardStatusBadge, SeverityBadge } from '@/components/shared/StatusBadge';
import { useAnonymousHazardStatus } from '@/hooks/useHazards';

/**
 * Public, no-auth status check page for anonymous hazard reporters.
 *
 *   /hazard-status?id=<uuid> → fetches GET /api/v1/hazard-reports/anonymous/{id}
 *   via the publicApi instance (no bearer, no session cookie attached).
 *
 * Critical contract: ONLY public_updates appear here. Internal notes
 * never reach this endpoint server-side; the page just renders what
 * the backend returns.
 */
export function PublicHazardStatusPage() {
  const { t, i18n } = useTranslation();
  const [searchParams, setSearchParams] = useSearchParams();
  const initialId = searchParams.get('id') ?? '';
  const [input, setInput] = useState(initialId);
  const [submittedId, setSubmittedId] = useState(initialId || undefined);

  const { data, isLoading, isError, error, isFetching } = useAnonymousHazardStatus(submittedId);

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    const value = input.trim();
    if (!value) return;
    setSubmittedId(value);
    setSearchParams({ id: value });
  }

  function toggleLang() {
    const next = i18n.language.startsWith('ar') ? 'en' : 'ar';
    void i18n.changeLanguage(next);
  }

  const status = data?.data;
  const notFound =
    isError && error instanceof ApiError && error.status === 404;

  return (
    <div className="min-h-screen bg-background flex flex-col">
      {/* Public header — no auth chrome */}
      <header className="border-b border-border">
        <div className="max-w-2xl mx-auto px-6 h-12 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="size-6 rounded-sm bg-foreground grid place-items-center text-background text-[11px] font-semibold">
              eP
            </div>
            <span className="text-sm font-medium">ePassport</span>
          </div>
          <button
            type="button"
            onClick={toggleLang}
            className="text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            {i18n.language.startsWith('ar') ? 'EN' : 'AR'}
          </button>
        </div>
      </header>

      <main className="flex-1 max-w-2xl mx-auto w-full px-6 py-10 space-y-6">
        <div className="flex items-start gap-3">
          <div className="size-9 shrink-0 rounded-md bg-success/10 grid place-items-center text-success">
            <ShieldCheck className="size-5" strokeWidth={2.25} />
          </div>
          <div>
            <h1 className="text-xl font-medium">
              {t('public_status.title', 'Check hazard report status')}
            </h1>
            <p className="text-sm text-muted-foreground mt-1">
              {t(
                'public_status.subtitle',
                'Enter the report ID you received when you submitted the hazard. Only public updates from the safety team are shown here.'
              )}
            </p>
          </div>
        </div>

        <form onSubmit={onSubmit} className="flex flex-col sm:flex-row gap-2">
          <div className="relative flex-1">
            <Search className="absolute start-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground pointer-events-none" />
            <Input
              type="text"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder={t(
                'public_status.placeholder',
                'Anonymous report ID (e.g. 7be1cbf1-…)'
              )}
              className="ps-8 mono"
            />
          </div>
          <Button type="submit" disabled={!input.trim() || isFetching}>
            {t('public_status.submit', 'Check status')}
          </Button>
        </form>

        {/* Result */}
        {submittedId && isLoading && (
          <p className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</p>
        )}

        {submittedId && notFound && (
          <Card>
            <CardContent className="py-8 text-center text-sm">
              <p className="text-foreground">
                {t('public_status.not_found', 'No hazard report found for that ID.')}
              </p>
              <p className="text-muted-foreground mt-1">
                {t(
                  'public_status.not_found_hint',
                  'Double-check the ID. It looks like a UUID such as 7be1cbf1-…'
                )}
              </p>
            </CardContent>
          </Card>
        )}

        {submittedId && status && (
          <div className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>
                  <div className="flex items-baseline gap-2 flex-wrap">
                    <span className="mono text-sm font-normal text-foreground">
                      {status.anonymous_report_id}
                    </span>
                  </div>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex flex-wrap items-center gap-2">
                  <HazardStatusBadge status={status.status} />
                  <SeverityBadge severity={status.severity} />
                  <span className="text-sm text-muted-foreground">
                    · {t(`hazards.category.${status.category}`, status.category)}
                  </span>
                </div>

                <dl className="grid grid-cols-[140px_1fr] gap-2 text-sm">
                  <dt className="text-muted-foreground text-xs">
                    {t('public_status.submitted', 'Submitted')}
                  </dt>
                  <dd className="mono tabular-nums">
                    {status.submitted_at?.replace('T', ' ').slice(0, 16) ?? '—'}
                  </dd>
                  {status.resolved_at && (
                    <>
                      <dt className="text-muted-foreground text-xs">
                        {t('public_status.resolved', 'Resolved')}
                      </dt>
                      <dd className="mono tabular-nums">
                        {status.resolved_at.replace('T', ' ').slice(0, 16)}
                      </dd>
                    </>
                  )}
                </dl>

                {status.resolution_summary && (
                  <div className="rounded-md border border-success/30 bg-success/5 px-3 py-2 text-sm">
                    <p className="text-[11px] uppercase tracking-wide text-success font-semibold">
                      {t('public_status.resolution_label', 'Resolution')}
                    </p>
                    <p className="mt-0.5 text-foreground whitespace-pre-wrap">
                      {status.resolution_summary}
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>{t('public_status.updates', 'Public updates')}</CardTitle>
              </CardHeader>
              <CardContent>
                {status.public_updates.length === 0 ? (
                  <p className="text-sm text-muted-foreground py-2">
                    {t(
                      'public_status.updates_empty',
                      'No public updates yet. Your report has been logged.'
                    )}
                  </p>
                ) : (
                  <ol className="space-y-2.5">
                    {status.public_updates.map((u, i) => (
                      <li
                        key={i}
                        className="rounded-md border border-border bg-muted/40 px-3 py-2"
                      >
                        <p className="text-sm whitespace-pre-wrap">{u.body}</p>
                        <p className="text-[11px] mono tabular-nums text-muted-foreground mt-1">
                          {u.posted_at.replace('T', ' ').slice(0, 16)}
                        </p>
                      </li>
                    ))}
                  </ol>
                )}
              </CardContent>
            </Card>
          </div>
        )}
      </main>

      <footer className="border-t border-border">
        <div className="max-w-2xl mx-auto px-6 py-4 text-[11px] text-muted-foreground">
          {t(
            'public_status.privacy_note',
            'This page never collects personal information. The anonymous report ID is the only thing we store about you.'
          )}
        </div>
      </footer>
    </div>
  );
}
