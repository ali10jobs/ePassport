import { useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import type { CertRangeParams, MainContractorDashboard } from '@/api/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/cn';

import { MetricCard } from './MetricCard';

const todayISO = () => new Date().toISOString().slice(0, 10);

export function MainContractorView({
  data,
  certRanges,
  onCertRangesChange,
}: {
  data: MainContractorDashboard;
  certRanges: CertRangeParams;
  onCertRangesChange: (next: CertRangeParams) => void;
}) {
  const { t } = useTranslation();

  return (
    <div className="space-y-6">
      <DashboardHeader
        title={t('dashboard.mc.title', 'Main contractor overview')}
        subtitle={t(
          'dashboard.mc.subtitle',
          'Workforce, equipment, permits, and hazards across your org and subcontractors.'
        )}
      />

      {/* Two-column layout matching design-reference.png — flat grid so the
          left card in each row stretches to match the height of the 4-card
          right cluster (Workforce ↔ Certifications, Equipment ↔ Permits). */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-1">
          <MetricCard
            title={t('dashboard.workforce', 'Workforce')}
            description={t('dashboard.workforce_desc', 'Yours + engaged subcontractors')}
            value={data.workers.mine + data.workers.subs}
            unit={t('dashboard.workers_unit', 'workers')}
            href="/workers"
            breakdown={[
              { label: t('dashboard.mine', 'Mine'), value: data.workers.mine },
              { label: t('dashboard.subs', 'Subs'), value: data.workers.subs },
            ]}
            className="h-full"
          />
        </div>
        <div className="lg:col-span-2">
          <CertExpiryRangeSection
            certs={data.certifications}
            certRanges={certRanges}
            onCertRangesChange={onCertRangesChange}
          />
        </div>

        <div className="lg:col-span-1">
          <MetricCard
            title={t('dashboard.equipment', 'Equipment')}
            description={t('dashboard.equipment_desc', 'Owned by your org')}
            value={data.equipment.mine}
            unit={t('dashboard.equipment_unit', 'units')}
            tone={data.equipment.tpi_expired > 0 ? 'destructive' : 'default'}
            href="/equipment"
            breakdown={[
              {
                label: t('dashboard.tpi_expired', 'TPI expired'),
                value: data.equipment.tpi_expired,
              },
            ]}
            className="h-full"
          />
        </div>
        <div className="lg:col-span-2">
          <PermitsRow
            permits={{
              active_approved: data.permits.active_approved,
              awaiting_review: data.permits.awaiting_review,
              drafts: data.permits.drafts,
              recently_rejected: data.permits.recently_rejected,
            }}
          />
        </div>
      </div>

      {/* Bottom row mirrors the 3-col grid above so card widths line up */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-1">
          <MetricCard
            title={t('dashboard.hazards_assigned', 'Hazards assigned to us')}
            description={t('dashboard.hazards_assigned_desc', 'Open + lifetime total')}
            value={data.hazards.open_assigned_to_us}
            unit={t('dashboard.open_unit', 'open')}
            tone={data.hazards.open_assigned_to_us > 0 ? 'warning' : 'default'}
            href="/hazards?status=under_review"
            breakdown={[
              {
                label: t('dashboard.lifetime', 'Lifetime'),
                value: data.hazards.assigned_to_us,
              },
            ]}
            className="h-full"
          />
        </div>
        <div className="lg:col-span-2">
          <MetricCard
            title={t('dashboard.subs_count', 'Subcontractors')}
            description={t('dashboard.subs_count_desc', 'Engaged under your contracts')}
            value={data.subcontractors.length}
            className="h-full"
          />
        </div>
      </div>
    </div>
  );
}

export function DashboardHeader({
  title,
  subtitle,
}: {
  title: string;
  subtitle: string;
}) {
  return (
    <div>
      <h2 className="text-lg font-medium">{title}</h2>
      <p className="text-sm text-muted-foreground">{subtitle}</p>
    </div>
  );
}

/**
 * Two wide tiles with date-range pickers — one for already-expired certs,
 * one for upcoming expiries. The selected ranges are lifted to DashboardPage
 * so the dashboard query refetches with new query params.
 *
 * Falls back to the unfiltered totals (expired / expiring_90_days) when no
 * range is selected, so the tile is never empty.
 */
export function CertExpiryRangeSection({
  certs,
  certRanges,
  onCertRangesChange,
}: {
  certs: import('@/api/types').CertExpiryCounts;
  certRanges: CertRangeParams;
  onCertRangesChange: (next: CertRangeParams) => void;
}) {
  const { t } = useTranslation();

  const expiredCount = certs.expired_in_range ?? certs.expired;
  const expiringCount = certs.expiring_in_range ?? certs.expiring_90_days;
  const expiredFiltered = certs.expired_in_range !== null && certs.expired_in_range !== undefined;
  const expiringFiltered = certs.expiring_in_range !== null && certs.expiring_in_range !== undefined;

  const today = todayISO();

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-3 h-full">
      <RangeFilterCard
        title={t('dashboard.cert_expired_title', 'Expired certifications')}
        description={
          expiredFiltered
            ? t('dashboard.cert_expired_range_desc', 'Within the selected range')
            : t('dashboard.cert_expired_total_desc', 'All certifications past expiry')
        }
        value={expiredCount}
        tone={expiredCount > 0 ? 'destructive' : 'default'}
        from={certRanges.expired_from ?? ''}
        to={certRanges.expired_to ?? ''}
        maxDate={today}
        onChange={(from, to) =>
          onCertRangesChange({
            ...certRanges,
            expired_from: from || undefined,
            expired_to: to || undefined,
          })
        }
        onClear={() =>
          onCertRangesChange({
            ...certRanges,
            expired_from: undefined,
            expired_to: undefined,
          })
        }
      />
      <RangeFilterCard
        title={t('dashboard.cert_expiring_title', 'Certifications expiring in')}
        description={
          expiringFiltered
            ? t('dashboard.cert_expiring_range_desc', 'Within the selected range')
            : t('dashboard.cert_expiring_total_desc', 'Next 90 days (default)')
        }
        value={expiringCount}
        tone={expiringCount > 0 ? 'warning' : 'default'}
        from={certRanges.expiring_from ?? ''}
        to={certRanges.expiring_to ?? ''}
        minDate={today}
        onChange={(from, to) =>
          onCertRangesChange({
            ...certRanges,
            expiring_from: from || undefined,
            expiring_to: to || undefined,
          })
        }
        onClear={() =>
          onCertRangesChange({
            ...certRanges,
            expiring_from: undefined,
            expiring_to: undefined,
          })
        }
      />
    </div>
  );
}

function RangeFilterCard({
  title,
  description,
  value,
  tone,
  from,
  to,
  minDate,
  maxDate,
  onChange,
  onClear,
}: {
  title: string;
  description: string;
  value: number;
  tone: 'default' | 'destructive' | 'warning' | 'success';
  from: string;
  to: string;
  minDate?: string;
  maxDate?: string;
  onChange: (from: string, to: string) => void;
  onClear: () => void;
}) {
  const valueColor = cn(
    'mono tabular-nums text-3xl font-medium leading-none',
    tone === 'destructive' && 'text-destructive',
    tone === 'warning' && 'text-warning-foreground',
    tone === 'success' && 'text-success'
  );

  return (
    <Card className="h-full flex flex-col">
      <CardHeader>
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
              {title}
            </CardTitle>
            <p className="text-xs text-muted-foreground/80 mt-1 leading-snug">{description}</p>
          </div>
          <DurationPicker
            from={from}
            to={to}
            minDate={minDate}
            maxDate={maxDate}
            onChange={onChange}
            onClear={onClear}
          />
        </div>
      </CardHeader>
      <CardContent className="pt-0">
        <span className={valueColor}>{value}</span>
      </CardContent>
    </Card>
  );
}

/**
 * Single date input that captures a range in two clicks. The label flips
 * from "From" → "Until" between picks so the user sees what the next click
 * will set. After the second pick, the range is committed and the label
 * shows "from → until".
 *
 * Implementation: one visible <input type="date"> drives both steps. We
 * track which step is active in local state. On the first change we stash
 * the value as a pending "from", reset the input, and switch the label to
 * "Until" — clicking the input again opens the picker for the second date.
 */
function DurationPicker({
  from,
  to,
  minDate,
  maxDate,
  onChange,
  onClear,
}: {
  from: string;
  to: string;
  minDate?: string;
  maxDate?: string;
  onChange: (from: string, to: string) => void;
  onClear: () => void;
}) {
  const { t } = useTranslation();
  const inputRef = useRef<HTMLInputElement>(null);
  // Pending "from" date while we're waiting for the user to pick "until".
  const [pendingFrom, setPendingFrom] = useState<string | null>(null);

  const awaitingUntil = pendingFrom !== null;
  const hasRange = !!from && !!to;

  const label = awaitingUntil
    ? t('dashboard.range_until', 'Until')
    : hasRange
      ? `${from} → ${to}`
      : t('dashboard.range_from', 'From');

  const openPicker = () => {
    const el = inputRef.current;
    if (!el) return;
    if (typeof el.showPicker === 'function') {
      el.showPicker();
    } else {
      el.focus();
      el.click();
    }
  };

  const handleChange = (next: string) => {
    if (!next) return;
    if (!awaitingUntil) {
      // First pick → stash as pending From, prompt for Until next.
      setPendingFrom(next);
      // Clear the input value so the calendar reopens cleanly for step 2.
      if (inputRef.current) inputRef.current.value = '';
      setTimeout(openPicker, 50);
      return;
    }
    // Second pick → commit. Service normalizes order so picking earlier
    // is fine, but we still pass in the order the user chose.
    onChange(pendingFrom!, next);
    setPendingFrom(null);
    if (inputRef.current) inputRef.current.value = '';
  };

  const handleClear = () => {
    setPendingFrom(null);
    if (inputRef.current) inputRef.current.value = '';
    onClear();
  };

  return (
    <div className="flex flex-col items-start gap-1 shrink-0">
      <label className="flex flex-col items-start text-[10px] uppercase tracking-wide text-muted-foreground">
        {label}
        <input
          ref={inputRef}
          type="date"
          min={minDate}
          max={maxDate}
          onChange={(e) => handleChange(e.target.value)}
          onClick={() => {
            // On some browsers click already opens the picker, but calling
            // showPicker() here makes the behavior reliable across all of them.
            openPicker();
          }}
          className="mt-1 h-8 rounded-md border border-input bg-background px-2 text-xs"
        />
      </label>
      {(hasRange || awaitingUntil) && (
        <button
          type="button"
          onClick={handleClear}
          className="text-[10px] uppercase tracking-wide text-muted-foreground hover:text-foreground underline-offset-2 hover:underline"
        >
          {t('dashboard.range_clear', 'Clear')}
        </button>
      )}
    </div>
  );
}

export function CertExpirySection({
  title,
  certs,
}: {
  title: string;
  certs: import('@/api/types').CertExpiryCounts;
}) {
  const { t } = useTranslation();
  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-3 h-full">
      <MetricCard
        title={title}
        description={t('dashboard.cert_expired_desc', 'Already expired')}
        value={certs.expired}
        tone={certs.expired > 0 ? 'destructive' : 'default'}
        href="/workers?cert_status=expired"
        className="h-full"
      />
      <MetricCard
        title={t('dashboard.cert_30', '30 days')}
        value={certs.expiring_30_days}
        tone={certs.expiring_30_days > 0 ? 'warning' : 'default'}
        className="h-full"
      />
      <MetricCard
        title={t('dashboard.cert_60', '60 days')}
        value={certs.expiring_60_days}
        className="h-full"
      />
      <MetricCard
        title={t('dashboard.cert_90', '90 days')}
        value={certs.expiring_90_days}
        className="h-full"
      />
    </div>
  );
}

export function PermitsRow({
  permits,
}: {
  permits: {
    active_approved: number;
    awaiting_review: number;
    drafts?: number;
    recently_rejected?: number;
    closed_this_week?: number;
  };
}) {
  const { t } = useTranslation();
  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-3 h-full">
      <MetricCard
        title={t('dashboard.permits_active', 'Active permits')}
        value={permits.active_approved}
        tone="success"
        href="/permits?status=approved"
        className="h-full"
      />
      <MetricCard
        title={t('dashboard.permits_awaiting', 'Awaiting review')}
        value={permits.awaiting_review}
        tone={permits.awaiting_review > 0 ? 'warning' : 'default'}
        href="/permits?status=submitted"
        className="h-full"
      />
      {permits.drafts !== undefined && (
        <MetricCard
          title={t('dashboard.permits_drafts', 'Drafts')}
          value={permits.drafts}
          href="/permits?status=draft"
          className="h-full"
        />
      )}
      {permits.recently_rejected !== undefined && (
        <MetricCard
          title={t('dashboard.permits_rejected', 'Rejected (7d)')}
          value={permits.recently_rejected}
          tone={permits.recently_rejected > 0 ? 'destructive' : 'default'}
          href="/permits?status=rejected"
          className="h-full"
        />
      )}
      {permits.closed_this_week !== undefined && (
        <MetricCard
          title={t('dashboard.permits_closed_week', 'Closed this week')}
          value={permits.closed_this_week}
          href="/permits?status=closed"
          className="h-full"
        />
      )}
    </div>
  );
}
