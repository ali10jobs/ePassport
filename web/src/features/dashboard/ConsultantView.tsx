import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import type { ConsultantDashboard } from '@/api/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { DashboardHeader } from './MainContractorView';
import { MetricCard } from './MetricCard';

export function ConsultantView({ data }: { data: ConsultantDashboard }) {
  const { t } = useTranslation();

  return (
    <div className="space-y-6">
      <DashboardHeader
        title={t('dashboard.consultant.title', 'Consultant overview')}
        subtitle={t(
          'dashboard.consultant.subtitle',
          'Permits awaiting your review and hazard reports across supervised projects.'
        )}
      />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <MetricCard
          title={t('dashboard.permits_awaiting', 'Awaiting review')}
          description={t('dashboard.consultant.queue_desc', 'Submitted, waiting for your decision')}
          value={data.permits.awaiting_review}
          tone={data.permits.awaiting_review > 0 ? 'warning' : 'default'}
          href="/permits?status=submitted"
        />
        <MetricCard
          title={t('dashboard.permits_approved_today', 'Approved today')}
          value={data.permits.approved_today}
          tone="success"
        />
        <MetricCard
          title={t('dashboard.permits_rejected_today', 'Rejected today')}
          value={data.permits.rejected_today}
          tone={data.permits.rejected_today > 0 ? 'destructive' : 'default'}
        />
      </div>

      {/* Top-of-queue permits awaiting review */}
      <Card>
        <CardHeader>
          <CardTitle>{t('dashboard.consultant.top_awaiting', 'Top of queue')}</CardTitle>
          <CardDescription>
            {t(
              'dashboard.consultant.top_awaiting_desc',
              'Oldest submitted permits awaiting your decision.'
            )}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {data.permits.top_awaiting.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              {t('dashboard.consultant.queue_empty', 'Queue is clear.')}
            </p>
          ) : (
            <ol className="divide-y divide-border -mx-4">
              {data.permits.top_awaiting.map((p) => (
                <li key={p.id}>
                  <Link
                    to={`/permits/${p.id}`}
                    className="flex items-center justify-between gap-4 px-4 py-2.5 hover:bg-muted/40 transition-colors"
                  >
                    <span className="mono text-sm">{p.permit_number}</span>
                    <span className="mono tabular-nums text-xs text-muted-foreground">
                      {p.submitted_at?.replace('T', ' ').slice(0, 16) ?? '—'}
                    </span>
                  </Link>
                </li>
              ))}
            </ol>
          )}
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <MetricCard
          title={t('dashboard.hazards_mtd', 'Hazards this month')}
          value={data.hazards.submitted_mtd}
          href="/hazards"
        />
        <MetricCard
          title={t('dashboard.hazards_critical_open', 'Critical hazards open')}
          value={data.hazards.open_critical}
          tone={data.hazards.open_critical > 0 ? 'destructive' : 'default'}
          href="/hazards?severity=critical"
        />
        <MetricCard
          title={t('dashboard.red_scans_today', 'Red scans today')}
          value={data.scans.red_scans_today}
          tone={data.scans.red_scans_today > 0 ? 'destructive' : 'default'}
          href="/scans"
        />
      </div>

      <MetricCard
        title={t('dashboard.scans_24h', 'Scans (24h)')}
        value={data.scans.total_24h}
        breakdown={[
          { label: t('dashboard.green', 'Green'), value: data.scans.green_24h },
          { label: t('dashboard.red', 'Red'), value: data.scans.red_24h },
          {
            label: t('dashboard.impersonation', 'Impersonation'),
            value: data.scans.impersonation_24h,
          },
        ]}
      />
    </div>
  );
}
