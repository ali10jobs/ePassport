import { useTranslation } from 'react-i18next';

import { Badge } from '@/components/ui/badge';

type CertStatus = 'valid' | 'expiring_soon' | 'expired';

interface CertStatusBadgeProps {
  status: CertStatus | string;
}

/**
 * StatusBadge specialised for certification status. Uses semantic
 * colors from the design tokens (success / warning / destructive).
 */
export function CertStatusBadge({ status }: CertStatusBadgeProps) {
  const { t } = useTranslation();

  if (status === 'expired') {
    return <Badge variant="destructive">{t('status.cert.expired', 'Expired')}</Badge>;
  }
  if (status === 'expiring_soon') {
    return <Badge variant="warning">{t('status.cert.expiring_soon', 'Expiring soon')}</Badge>;
  }
  return <Badge variant="success">{t('status.cert.valid', 'Valid')}</Badge>;
}

interface InductionStatusBadgeProps {
  status: string;
}

export function InductionStatusBadge({ status }: InductionStatusBadgeProps) {
  const { t } = useTranslation();
  if (status === 'inducted') {
    return <Badge variant="success">{t('status.induction.inducted', 'Inducted')}</Badge>;
  }
  if (status === 'expired') {
    return <Badge variant="destructive">{t('status.induction.expired', 'Expired')}</Badge>;
  }
  return <Badge variant="warning">{t('status.induction.not_inducted', 'Not inducted')}</Badge>;
}
