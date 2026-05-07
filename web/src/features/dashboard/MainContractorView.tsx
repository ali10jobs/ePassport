import { useTranslation } from 'react-i18next';

import type { MainContractorDashboard } from '@/api/types';

import { MetricCard } from './MetricCard';

export function MainContractorView({ data }: { data: MainContractorDashboard }) {
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

      {/* Two-column layout matching design-reference.png */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="space-y-4 lg:col-span-1">
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
          />
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
          />
        </div>

        <div className="lg:col-span-2 space-y-4">
          <CertExpirySection
            title={t('dashboard.certs', 'Certifications expiring')}
            certs={data.certifications}
          />
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
          />
        </div>
        <div className="lg:col-span-2">
          <MetricCard
            title={t('dashboard.subs_count', 'Subcontractors')}
            description={t('dashboard.subs_count_desc', 'Engaged under your contracts')}
            value={data.subcontractors.length}
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

export function CertExpirySection({
  title,
  certs,
}: {
  title: string;
  certs: import('@/api/types').CertExpiryCounts;
}) {
  const { t } = useTranslation();
  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
      <MetricCard
        title={title}
        description={t('dashboard.cert_expired_desc', 'Already expired')}
        value={certs.expired}
        tone={certs.expired > 0 ? 'destructive' : 'default'}
        href="/workers?cert_status=expired"
      />
      <MetricCard
        title={t('dashboard.cert_30', '30 days')}
        value={certs.expiring_30_days}
        tone={certs.expiring_30_days > 0 ? 'warning' : 'default'}
      />
      <MetricCard
        title={t('dashboard.cert_60', '60 days')}
        value={certs.expiring_60_days}
      />
      <MetricCard
        title={t('dashboard.cert_90', '90 days')}
        value={certs.expiring_90_days}
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
    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
      <MetricCard
        title={t('dashboard.permits_active', 'Active permits')}
        value={permits.active_approved}
        tone="success"
        href="/permits?status=approved"
      />
      <MetricCard
        title={t('dashboard.permits_awaiting', 'Awaiting review')}
        value={permits.awaiting_review}
        tone={permits.awaiting_review > 0 ? 'warning' : 'default'}
        href="/permits?status=submitted"
      />
      {permits.drafts !== undefined && (
        <MetricCard
          title={t('dashboard.permits_drafts', 'Drafts')}
          value={permits.drafts}
          href="/permits?status=draft"
        />
      )}
      {permits.recently_rejected !== undefined && (
        <MetricCard
          title={t('dashboard.permits_rejected', 'Rejected (7d)')}
          value={permits.recently_rejected}
          tone={permits.recently_rejected > 0 ? 'destructive' : 'default'}
          href="/permits?status=rejected"
        />
      )}
      {permits.closed_this_week !== undefined && (
        <MetricCard
          title={t('dashboard.permits_closed_week', 'Closed this week')}
          value={permits.closed_this_week}
          href="/permits?status=closed"
        />
      )}
    </div>
  );
}
