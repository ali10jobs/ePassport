import { ChevronLeft, ChevronRight, Plus, Search } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { PermitStatusBadge } from '@/components/shared/StatusBadge';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { usePermits } from '@/hooks/usePermits';

const PER_PAGE = 25;

export function PermitsListPage() {
  const { t } = useTranslation();
  const [page, setPage] = useState(1);
  const [searchInput, setSearchInput] = useState('');
  const [status, setStatus] = useState('');

  const search = useDebouncedValue(searchInput, 250);

  const { data, isLoading, isError, error, isFetching } = usePermits({
    page,
    perPage: PER_PAGE,
    search: search || undefined,
    status: status || undefined,
  });

  return (
    <div className="space-y-4">
      <div className="flex items-end justify-between gap-4">
        <div>
          <h2 className="text-lg font-medium">{t('nav.permits', 'Permits')}</h2>
          <p className="text-sm text-muted-foreground">
            {t('permits.subtitle', 'Permit-to-Work issuances and their lifecycle.')}
          </p>
        </div>
        <Link to="/permits/new">
          <Button size="sm">
            <Plus className="size-3.5 me-1" />
            {t('permits.new', 'New permit')}
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
            placeholder={t('permits.search_placeholder', 'Search by permit number or scope…')}
            className="ps-8"
          />
        </div>
        <Select
          value={status}
          onChange={(e) => {
            setStatus(e.target.value);
            setPage(1);
          }}
          className="w-44"
          aria-label={t('permits.filter_status', 'Status')}
        >
          <option value="">{t('permits.filter_status_all', 'Any status')}</option>
          <option value="draft">{t('status.permit.draft', 'Draft')}</option>
          <option value="submitted">{t('status.permit.submitted', 'Submitted')}</option>
          <option value="approved">{t('status.permit.approved', 'Approved')}</option>
          <option value="rejected">{t('status.permit.rejected', 'Rejected')}</option>
          <option value="closed">{t('status.permit.closed', 'Closed')}</option>
          <option value="expired">{t('status.permit.expired', 'Expired')}</option>
        </Select>
      </div>

      <Card>
        {isLoading ? (
          <div className="px-4 py-12 text-sm text-muted-foreground">
            {t('common.loading', 'Loading…')}
          </div>
        ) : isError ? (
          <div className="px-4 py-12 text-sm text-destructive">
            {(error as Error)?.message ??
              t('permits.error_loading', 'Could not load permits.')}
          </div>
        ) : (
          <>
            <Table>
              <THead>
                <TR>
                  <TH className="w-40">{t('permits.col_number', 'Permit number')}</TH>
                  <TH>{t('permits.col_type', 'Type')}</TH>
                  <TH>{t('permits.col_status', 'Status')}</TH>
                  <TH className="hidden md:table-cell">{t('permits.col_scope', 'Scope')}</TH>
                  <TH className="hidden lg:table-cell w-32">
                    {t('permits.col_workers', 'Workers')}
                  </TH>
                  <TH className="hidden lg:table-cell w-44">
                    {t('permits.col_submitted', 'Submitted')}
                  </TH>
                </TR>
              </THead>
              <TBody>
                {data?.data.map((p) => (
                  <TR key={p.id}>
                    <TD>
                      <Link
                        to={`/permits/${p.id}`}
                        className="mono text-foreground hover:underline"
                      >
                        {p.permit_number}
                      </Link>
                    </TD>
                    <TD>
                      <div className="flex flex-col gap-0.5 leading-tight">
                        <span>{p.permit_type?.name_en ?? '—'}</span>
                        <span className="mono text-[11px] text-muted-foreground">
                          {p.permit_type?.code ?? ''}
                        </span>
                      </div>
                    </TD>
                    <TD>
                      <PermitStatusBadge status={p.status} />
                    </TD>
                    <TD className="hidden md:table-cell text-muted-foreground max-w-[28rem] truncate">
                      {p.scope_en}
                    </TD>
                    <TD className="hidden lg:table-cell mono tabular-nums text-muted-foreground">
                      {p.workers_count ?? '—'}
                    </TD>
                    <TD className="hidden lg:table-cell mono tabular-nums text-muted-foreground">
                      {p.submitted_at?.slice(0, 16).replace('T', ' ') ?? '—'}
                    </TD>
                  </TR>
                ))}
                {data?.data.length === 0 && (
                  <TR>
                    <TD colSpan={6} className="text-center text-sm text-muted-foreground py-12">
                      {t('permits.empty', 'No permits match your filters.')}
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
