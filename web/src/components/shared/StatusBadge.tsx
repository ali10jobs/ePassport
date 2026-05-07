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

interface PermitStatusBadgeProps {
  status: string;
}

/**
 * Permit lifecycle badge — the colors map to demo expectations:
 *   draft     -> neutral outline
 *   submitted -> primary (blue) — awaiting consultant review
 *   approved  -> success (green) — work can proceed
 *   rejected  -> destructive (red)
 *   closed    -> neutral fill — work complete
 *   expired   -> warning (amber)
 */
export function PermitStatusBadge({ status }: PermitStatusBadgeProps) {
  const { t } = useTranslation();
  const variant = (() => {
    switch (status) {
      case 'approved':
        return 'success' as const;
      case 'submitted':
        return 'primary' as const;
      case 'rejected':
        return 'destructive' as const;
      case 'closed':
        return 'neutral' as const;
      case 'expired':
        return 'warning' as const;
      case 'draft':
      default:
        return 'outline' as const;
    }
  })();
  return <Badge variant={variant}>{t(`status.permit.${status}`, status)}</Badge>;
}
