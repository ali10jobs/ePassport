import { useTranslation } from 'react-i18next';

import type { SubcontractorDashboard } from '@/api/types';

import { CertExpirySection, DashboardHeader } from './MainContractorView';
import { MetricCard } from './MetricCard';

export function SubcontractorView({ data }: { data: SubcontractorDashboard }) {
  const { t } = useTranslation();

  return (
    <div className="space-y-6">
      <DashboardHeader
        title={t('dashboard.sub.title', 'Subcontractor overview')}
        subtitle={t('dashboard.sub.subtitle', 'Your workforce and equipment.')}
      />

      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <MetricCard
          title={t('dashboard.workforce', 'Workforce')}
          value={data.workers.total}
          unit={t('dashboard.workers_unit', 'workers')}
          href="/workers"
          breakdown={[
            { label: t('dashboard.inducted', 'Inducted'), value: data.workers.inducted },
            {
              label: t('dashboard.not_inducted', 'Not inducted'),
              value: data.workers.not_inducted,
            },
          ]}
        />
        <MetricCard
          title={t('dashboard.equipment', 'Equipment')}
          value={data.equipment.mine}
          unit={t('dashboard.equipment_unit', 'units')}
          href="/equipment"
        />
        <MetricCard
          title={t('dashboard.cert_expired', 'Certs expired')}
          value={data.certifications.expired}
          tone={data.certifications.expired > 0 ? 'destructive' : 'default'}
          href="/workers?cert_status=expired"
        />
      </div>

      <CertExpirySection
        title={t('dashboard.certs', 'Certifications expiring')}
        certs={data.certifications}
      />
    </div>
  );
}
