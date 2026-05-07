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
import { TpiStatusBadge } from '@/components/shared/StatusBadge';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { useEquipmentList } from '@/hooks/useEquipment';

const PER_PAGE = 25;

export function EquipmentListPage() {
  const { t } = useTranslation();
  const [page, setPage] = useState(1);
  const [searchInput, setSearchInput] = useState('');
  const [type, setType] = useState('');
  const [tpiStatus, setTpiStatus] = useState<'valid' | 'expired' | ''>('');

  const search = useDebouncedValue(searchInput, 250);

  const { data, isLoading, isError, error, isFetching } = useEquipmentList({
    page,
    perPage: PER_PAGE,
    search: search || undefined,
    type: type || undefined,
    tpiStatus: tpiStatus || undefined,
  });

  return (
    <div className="space-y-4">
      <div className="flex items-end justify-between gap-4">
        <div>
          <h2 className="text-lg font-medium">{t('nav.equipment', 'Equipment')}</h2>
          <p className="text-sm text-muted-foreground">
            {t(
              'equipment.subtitle',
              'Plant, lifting gear, and powered tools — TPI status surfaced at the gate.'
            )}
          </p>
        </div>
        <Button size="sm" disabled>
          {t('equipment.add', 'Add equipment')}
        </Button>
      </div>

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
              'equipment.search_placeholder',
              'Search by asset tag, serial, manufacturer, model…'
            )}
            className="ps-8"
          />
        </div>
        <Select
          value={type}
          onChange={(e) => {
            setType(e.target.value);
            setPage(1);
          }}
          className="w-44"
          aria-label={t('equipment.filter_type', 'Type')}
        >
          <option value="">{t('equipment.filter_type_all', 'Any type')}</option>
          <option value="crane">{t('equipment.type.crane', 'Crane')}</option>
          <option value="forklift">{t('equipment.type.forklift', 'Forklift')}</option>
          <option value="scaffolding">{t('equipment.type.scaffolding', 'Scaffolding')}</option>
          <option value="hoist">{t('equipment.type.hoist', 'Hoist')}</option>
          <option value="lifting_gear">{t('equipment.type.lifting_gear', 'Lifting gear')}</option>
          <option value="welding">{t('equipment.type.welding', 'Welding')}</option>
          <option value="power_tool">{t('equipment.type.power_tool', 'Power tool')}</option>
          <option value="other">{t('equipment.type.other', 'Other')}</option>
        </Select>
        <Select
          value={tpiStatus}
          onChange={(e) => {
            setTpiStatus(e.target.value as 'valid' | 'expired' | '');
            setPage(1);
          }}
          className="w-44"
          aria-label={t('equipment.filter_tpi', 'TPI')}
        >
          <option value="">{t('equipment.filter_tpi_all', 'Any TPI status')}</option>
          <option value="valid">{t('equipment.filter_tpi_valid', 'TPI valid')}</option>
          <option value="expired">{t('equipment.filter_tpi_expired', 'TPI expired/missing')}</option>
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
              t('equipment.error_loading', 'Could not load equipment.')}
          </div>
        ) : (
          <>
            <Table>
              <THead>
                <TR>
                  <TH className="w-40">{t('equipment.col_asset_tag', 'Asset tag')}</TH>
                  <TH>{t('equipment.col_type', 'Type')}</TH>
                  <TH>{t('equipment.col_make_model', 'Make / model')}</TH>
                  <TH>{t('equipment.col_owner', 'Owner')}</TH>
                  <TH>{t('equipment.col_swl', 'SWL')}</TH>
                  <TH>{t('equipment.col_tpi', 'TPI')}</TH>
                </TR>
              </THead>
              <TBody>
                {data?.data.map((eq) => (
                  <TR key={eq.id}>
                    <TD>
                      <Link
                        to={`/equipment/${eq.id}`}
                        className="mono text-foreground hover:underline"
                      >
                        {eq.asset_tag}
                      </Link>
                    </TD>
                    <TD>
                      <Badge variant="outline">{t(`equipment.type.${eq.type}`, eq.type)}</Badge>
                    </TD>
                    <TD className="text-muted-foreground">
                      {[eq.manufacturer, eq.model].filter(Boolean).join(' · ') || '—'}
                    </TD>
                    <TD className="text-muted-foreground">
                      {eq.owner_organization?.name_en ?? '—'}
                    </TD>
                    <TD className="mono tabular-nums text-muted-foreground">
                      {eq.safe_working_load_kg ? `${eq.safe_working_load_kg} kg` : '—'}
                    </TD>
                    <TD>
                      <TpiStatusBadge cert={eq.latest_certification ?? null} />
                    </TD>
                  </TR>
                ))}
                {data?.data.length === 0 && (
                  <TR>
                    <TD
                      colSpan={6}
                      className="text-sm text-muted-foreground text-center py-12"
                    >
                      {t('equipment.empty', 'No equipment matches your filters.')}
                    </TD>
                  </TR>
                )}
              </TBody>
            </Table>

            {data && data.meta.last_page > 1 && (
              <div className="flex items-center justify-between border-t border-border px-4 py-2 text-xs text-muted-foreground">
                <div className="tabular-nums">
                  {t('common.pagination', '{{from}}–{{to}} of {{total}}', {
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
