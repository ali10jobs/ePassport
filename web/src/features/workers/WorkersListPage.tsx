import { ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { InductionStatusBadge } from '@/components/shared/StatusBadge';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { useWorkers } from '@/hooks/useWorkers';

const PER_PAGE = 25;

export function WorkersListPage() {
  const { t } = useTranslation();
  const [page, setPage] = useState(1);
  const [searchInput, setSearchInput] = useState('');
  const [inductionStatus, setInductionStatus] = useState<string>('');
  const [certStatus, setCertStatus] = useState<'expired' | 'valid' | ''>('');

  const search = useDebouncedValue(searchInput, 250);

  const { data, isLoading, isError, error, isFetching } = useWorkers({
    page,
    perPage: PER_PAGE,
    search: search || undefined,
    inductionStatus: inductionStatus || undefined,
    certStatus: certStatus || undefined,
  });

  return (
    <div className="space-y-4">
      <div className="flex items-end justify-between gap-4">
        <div>
          <h2 className="text-lg font-medium">{t('nav.workers', 'Workers')}</h2>
          <p className="text-sm text-muted-foreground">
            {t(
              'workers.subtitle',
              'All workers in your organisation and the engaged subcontractors.'
            )}
          </p>
        </div>
        <Button size="sm" disabled>
          {t('workers.add', 'Add worker')}
        </Button>
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
            placeholder={t(
              'workers.search_placeholder',
              'Search by name, employee ID, national ID…'
            )}
            className="ps-8"
          />
        </div>
        <Select
          value={inductionStatus}
          onChange={(e) => {
            setInductionStatus(e.target.value);
            setPage(1);
          }}
          className="w-44"
          aria-label={t('workers.filter_induction', 'Induction status')}
        >
          <option value="">{t('workers.filter_induction_all', 'Any induction')}</option>
          <option value="inducted">{t('status.induction.inducted', 'Inducted')}</option>
          <option value="not_inducted">{t('status.induction.not_inducted', 'Not inducted')}</option>
          <option value="expired">{t('status.induction.expired', 'Expired')}</option>
        </Select>
        <Select
          value={certStatus}
          onChange={(e) => {
            setCertStatus(e.target.value as 'expired' | 'valid' | '');
            setPage(1);
          }}
          className="w-44"
          aria-label={t('workers.filter_certs', 'Certifications')}
        >
          <option value="">{t('workers.filter_certs_all', 'Any cert status')}</option>
          <option value="valid">{t('workers.filter_certs_valid', 'No expired certs')}</option>
          <option value="expired">{t('workers.filter_certs_expired', 'Has expired cert')}</option>
        </Select>
      </div>

      {/* Table */}
      <Card>
        {isLoading ? (
          <div className="px-4 py-12 text-sm text-muted-foreground">
            {t('common.loading', 'Loading…')}
          </div>
        ) : isError ? (
          <div className="px-4 py-12 text-sm text-destructive">
            {(error as Error)?.message ?? t('workers.error_loading', 'Could not load workers.')}
          </div>
        ) : (
          <>
            <Table>
              <THead>
                <TR>
                  <TH className="w-32">{t('workers.col_employee_id', 'ID')}</TH>
                  <TH>{t('workers.col_name', 'Name')}</TH>
                  <TH>{t('workers.col_trade', 'Trade')}</TH>
                  <TH>{t('workers.col_employer', 'Employer')}</TH>
                  <TH>{t('workers.col_nationality', 'Nat.')}</TH>
                  <TH>{t('workers.col_induction', 'Induction')}</TH>
                </TR>
              </THead>
              <TBody>
                {data?.data.map((w) => (
                  <TR key={w.id}>
                    <TD>
                      <Link
                        to={`/workers/${w.id}`}
                        className="mono text-foreground hover:underline"
                      >
                        {w.employee_id}
                      </Link>
                    </TD>
                    <TD>
                      <Link to={`/workers/${w.id}`} className="hover:underline">
                        {w.full_name_en}
                      </Link>
                    </TD>
                    <TD className="text-muted-foreground">{w.trade ?? '—'}</TD>
                    <TD className="text-muted-foreground">
                      {w.employer_organization?.name_en ?? '—'}
                    </TD>
                    <TD>
                      {w.nationality ? (
                        <Badge variant="outline" className="mono">
                          {w.nationality}
                        </Badge>
                      ) : (
                        <span className="text-muted-foreground">—</span>
                      )}
                    </TD>
                    <TD>
                      <InductionStatusBadge status={w.induction_status} />
                    </TD>
                  </TR>
                ))}
                {data?.data.length === 0 && (
                  <TR>
                    <TD
                      colSpan={6}
                      className="text-sm text-muted-foreground text-center py-12"
                    >
                      {t('workers.empty', 'No workers match your filters.')}
                    </TD>
                  </TR>
                )}
              </TBody>
            </Table>

            {/* Pagination */}
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
