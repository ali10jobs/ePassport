import { useTranslation } from 'react-i18next';

import type { ClientDashboard } from '@/api/types';

import {
  CertExpirySection,
  DashboardHeader,
  PermitsRow,
} from './MainContractorView';
import { MetricCard } from './MetricCard';

export function ClientView({ data }: { data: ClientDashboard }) {
  const { t } = useTranslation();

  const indicators = data.incident_indicators;
  const hasIncident =
    indicators.red_scans_today > 0 ||
    indicators.impersonation_flags_today > 0 ||
    indicators.critical_hazards_open > 0;

  return (
    <div className="space-y-6">
      <DashboardHeader
        title={t('dashboard.client.title', 'Client overview')}
        subtitle={t(
          'dashboard.client.subtitle',
          'Cross-contractor metrics across the projects you own.'
        )}
      />

      {/* Top row: incident indicators — the loudest tile when something is wrong */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <MetricCard
          title={t('dashboard.red_scans_today', 'Red scans today')}
          description={t('dashboard.red_scans_today_desc', 'Workers turned away at the gate')}
          value={indicators.red_scans_today}
          tone={indicators.red_scans_today > 0 ? 'destructive' : 'default'}
          href="/scans"
        />
        <MetricCard
          title={t('dashboard.impersonation_today', 'Impersonation flags today')}
          description={t(
            'dashboard.impersonation_today_desc',
            'Helmet+coverall mismatch detected'
          )}
          value={indicators.impersonation_flags_today}
          tone={indicators.impersonation_flags_today > 0 ? 'destructive' : 'default'}
          href="/scans"
        />
        <MetricCard
          title={t('dashboard.critical_hazards', 'Critical hazards open')}
          description={t('dashboard.critical_hazards_desc', 'Severity = critical, not yet resolved')}
          value={indicators.critical_hazards_open}
          tone={indicators.critical_hazards_open > 0 ? 'destructive' : 'default'}
          href="/hazards?severity=critical"
        />
      </div>

      {!hasIncident && (
        <p className="text-xs text-muted-foreground -mt-2">
          {t('dashboard.no_incidents_today', 'No incident indicators today.')}
        </p>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="space-y-4 lg:col-span-1">
          <MetricCard
            title={t('dashboard.workforce', 'Workforce')}
            description={t('dashboard.workforce_client_desc', 'Across all engaged contractors')}
            value={data.workers.total}
            unit={t('dashboard.workers_unit', 'workers')}
            href="/workers"
            breakdown={Object.entries(data.workers.by_organization)
              .slice(0, 3)
              .map(([orgId, count]) => ({
                label: orgId.slice(0, 8) + '…',
                value: count,
              }))}
          />
          <MetricCard
            title={t('dashboard.projects', 'Projects')}
            description={t('dashboard.projects_owned', 'Owned by your org')}
            value={data.project_ids.length}
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
              closed_this_week: data.permits.closed_this_week,
            }}
          />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <MetricCard
          title={t('dashboard.hazards_mtd', 'Hazards this month')}
          value={data.hazards.submitted_mtd}
          href="/hazards"
        />
        <MetricCard
          title={t('dashboard.hazards_resolved_mtd', 'Resolved this month')}
          value={data.hazards.resolved_mtd}
          tone="success"
          href="/hazards?status=resolved"
        />
        <MetricCard
          title={t('dashboard.scans_24h', 'Scans (24h)')}
          value={data.scans.total_24h}
          breakdown={[
            { label: t('dashboard.green', 'Green'), value: data.scans.green_24h },
            { label: t('dashboard.red', 'Red'), value: data.scans.red_24h },
          ]}
        />
      </div>
    </div>
  );
}
