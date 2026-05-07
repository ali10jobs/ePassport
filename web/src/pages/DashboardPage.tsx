import { useTranslation } from 'react-i18next';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

/**
 * Dashboard placeholder — laid out to mirror design-reference.png:
 * left column with two stacked panels (Usage + Alerts), right column
 * with a primary feed. Real metrics arrive from
 * /api/v1/dashboards/{role}/summary in a later phase.
 */
export function DashboardPage() {
  const { t } = useTranslation();

  return (
    <div className="space-y-6">
      <div className="flex items-end justify-between">
        <div>
          <h2 className="text-lg font-medium">{t('nav.dashboard', 'Dashboard')}</h2>
          <p className="text-sm text-muted-foreground">
            {t('dashboard.subtitle', 'Overview of safety metrics across your active projects.')}
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div className="lg:col-span-1 space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>{t('dashboard.usage', 'Usage')}</CardTitle>
              <CardDescription>{t('dashboard.last_30_days', 'Last 30 days')}</CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm">
                <Stat label={t('dashboard.scans', 'Scans')} value="—" />
                <Stat label={t('dashboard.permits_open', 'Open permits')} value="—" />
                <Stat label={t('dashboard.hazards_mtd', 'Hazards MTD')} value="—" />
              </ul>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>{t('dashboard.alerts', 'Alerts')}</CardTitle>
              <CardDescription>
                {t('dashboard.alerts_description', 'Anomalies and overdue checks.')}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground">
                {t('dashboard.alerts_empty', 'No alerts. The data layer wires in next phase.')}
              </p>
            </CardContent>
          </Card>
        </div>

        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>{t('dashboard.recent', 'Recent activity')}</CardTitle>
            <CardDescription>
              {t('dashboard.recent_description', 'Scans, permit transitions, hazard reports.')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              {t('dashboard.recent_empty', 'Nothing here yet.')}
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function Stat({ label, value }: { label: string; value: string }) {
  return (
    <li className="flex items-center justify-between">
      <span className="text-muted-foreground">{label}</span>
      <span className="mono tabular-nums">{value}</span>
    </li>
  );
}
