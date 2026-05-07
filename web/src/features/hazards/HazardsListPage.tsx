import { ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { HazardStatusBadge, SeverityBadge } from '@/components/shared/StatusBadge';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { useHazards } from '@/hooks/useHazards';

const PER_PAGE = 25;

const SEVERITIES = ['low', 'medium', 'high', 'critical'] as const;
const STATUSES = ['submitted', 'under_review', 'action_issued', 'resolved', 'dismissed'] as const;
const CATEGORIES = [
  'fall',
  'electrical',
  'fire',
  'working_at_heights',
  'lifting',
  'housekeeping',
  'ppe',
  'environmental',
  'other',
] as const;

export function HazardsListPage() {
  const { t } = useTranslation();
  const [page, setPage] = useState(1);
  const [searchInput, setSearchInput] = useState('');
  const [severity, setSeverity] = useState('');
  const [status, setStatus] = useState('');
  const [category, setCategory] = useState('');

  const search = useDebouncedValue(searchInput, 250);

  const { data, isLoading, isError, error, isFetching } = useHazards({
    page,
    perPage: PER_PAGE,
    status: status || undefined,
    severity: severity || undefined,
    category: category || undefined,
    search: search || undefined,
  });

  return (
    <div className="space-y-4">
      <div className="flex items-end justify-between gap-4">
        <div>
          <h2 className="text-lg font-medium">{t('nav.hazards', 'Hazards')}</h2>
          <p className="text-sm text-muted-foreground">
            {t(
              'hazards.subtitle',
              'Hazard reports submitted on your projects. Anonymous submitters can check status separately.'
            )}
          </p>
        </div>
        <Link to="/hazard-status" target="_blank">
          <Button variant="secondary" size="sm">
            {t('hazards.public_status_link', 'Public status check')}
          </Button>
        </Link>
      </div>

      {/* Filter row */}
      <div className="flex flex-wrap items-center gap-2">
        <div className="relative flex-1 min-w-64">
          <Search className="absolute start-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground pointer-events-none" />
          <Input
            type="search"
            value={searchInput}
            onChange={(e) => {
              setSearchInput(e.target.value);
              setPage(1);
            }}
            placeholder={t('hazards.search_placeholder', 'Search description…')}
            className="ps-8"
          />
        </div>
        <Select
          value={severity}
          onChange={(e) => {
            setSeverity(e.target.value);
            setPage(1);
          }}
          className="w-36"
          aria-label={t('hazards.filter_severity', 'Severity')}
        >
          <option value="">{t('hazards.filter_severity_all', 'Any severity')}</option>
          {SEVERITIES.map((s) => (
            <option key={s} value={s}>
              {t(`status.severity.${s}`, s)}
            </option>
          ))}
        </Select>
        <Select
          value={status}
          onChange={(e) => {
            setStatus(e.target.value);
            setPage(1);
          }}
          className="w-44"
          aria-label={t('hazards.filter_status', 'Status')}
        >
          <option value="">{t('hazards.filter_status_all', 'Any status')}</option>
          {STATUSES.map((s) => (
            <option key={s} value={s}>
              {t(`status.hazard.${s}`, s.replace(/_/g, ' '))}
            </option>
          ))}
        </Select>
        <Select
          value={category}
          onChange={(e) => {
            setCategory(e.target.value);
            setPage(1);
          }}
          className="w-44"
          aria-label={t('hazards.filter_category', 'Category')}
        >
          <option value="">{t('hazards.filter_category_all', 'Any category')}</option>
          {CATEGORIES.map((c) => (
            <option key={c} value={c}>
              {t(`hazards.category.${c}`, c.replace(/_/g, ' '))}
            </option>
          ))}
        </Select>
      </div>

      <Card>
        {isLoading ? (
          <div className="px-4 py-12 text-sm text-muted-foreground">
            {t('common.loading', 'Loading…')}
          </div>
        ) : isError ? (
          <div className="px-4 py-12 text-sm text-destructive">
            {(error as Error)?.message ?? t('hazards.error_loading', 'Could not load hazards.')}
          </div>
        ) : (
          <>
            <Table>
              <THead>
                <TR>
                  <TH className="w-32">{t('hazards.col_id', 'ID')}</TH>
                  <TH>{t('hazards.col_severity', 'Severity')}</TH>
                  <TH>{t('hazards.col_category', 'Category')}</TH>
                  <TH>{t('hazards.col_status', 'Status')}</TH>
                  <TH className="hidden md:table-cell">
                    {t('hazards.col_description', 'Description')}
                  </TH>
                  <TH className="hidden lg:table-cell w-44">
                    {t('hazards.col_submitted', 'Submitted')}
                  </TH>
                </TR>
              </THead>
              <TBody>
                {data?.data.map((h) => (
                  <TR key={h.id}>
                    <TD>
                      <Link
                        to={`/hazards/${h.id}`}
                        className="mono text-foreground hover:underline"
                        title={h.anonymous_report_id}
                      >
                        {h.anonymous_report_id.slice(0, 8)}…
                      </Link>
                    </TD>
                    <TD>
                      <SeverityBadge severity={h.severity} />
                    </TD>
                    <TD className="text-muted-foreground">
                      {t(`hazards.category.${h.category}`, h.category)}
                    </TD>
                    <TD>
                      <HazardStatusBadge status={h.status} />
                    </TD>
                    <TD className="hidden md:table-cell text-muted-foreground max-w-[28rem] truncate">
                      {h.description ?? '—'}
                    </TD>
                    <TD className="hidden lg:table-cell mono tabular-nums text-muted-foreground">
                      {h.created_at?.slice(0, 16).replace('T', ' ') ?? '—'}
                    </TD>
                  </TR>
                ))}
                {data?.data.length === 0 && (
                  <TR>
                    <TD
                      colSpan={6}
                      className="text-center text-sm text-muted-foreground py-12"
                    >
                      {t('hazards.empty', 'No hazards match your filters.')}
                    </TD>
                  </TR>
                )}
              </TBody>
            </Table>

            {data && data.meta.last_page > 1 && (
              <div className="flex items-center justify-between border-t border-border px-4 py-2 text-xs text-muted-foreground">
                <div className="tabular-nums">
                  {t('workers.pagination', '{{from}}–{{to}} of {{total}}', {
                    from: data.meta.from ?? 0,
                    to: data.meta.to ?? 0,
                    total: data.meta.total,
                  })}
                  {isFetching && (
                    <span className="ms-2 text-muted-foreground/60">
                      {t('common.loading', 'Loading…')}
                    </span>
                  )}
                </div>
                <div className="flex items-center gap-1">
                  <Button
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    aria-label={t('common.previous', 'Previous')}
                    disabled={page <= 1}
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                  >
                    <ChevronLeft className="size-3.5 rtl:rotate-180" />
                  </Button>
                  <span className="mono px-2 tabular-nums">
                    {data.meta.current_page} / {data.meta.last_page}
                  </span>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    aria-label={t('common.next', 'Next')}
                    disabled={page >= data.meta.last_page}
                    onClick={() => setPage((p) => p + 1)}
                  >
                    <ChevronRight className="size-3.5 rtl:rotate-180" />
                  </Button>
                </div>
              </div>
            )}
          </>
        )}
      </Card>
    </div>
  );
}
