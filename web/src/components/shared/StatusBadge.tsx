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

interface SeverityBadgeProps {
  severity: string;
}

/**
 * Hazard severity badge — louder for critical/high, calmer for low/medium
 * so a long list of hazards reads at a glance.
 */
export function SeverityBadge({ severity }: SeverityBadgeProps) {
  const { t } = useTranslation();
  const variant = (() => {
    switch (severity) {
      case 'critical':
      case 'high':
        return 'destructive' as const;
      case 'medium':
        return 'warning' as const;
      case 'low':
      default:
        return 'neutral' as const;
    }
  })();
  return <Badge variant={variant}>{t(`status.severity.${severity}`, severity)}</Badge>;
}

interface HazardStatusBadgeProps {
  status: string;
}

/**
 * Hazard lifecycle badge:
 *   submitted     -> primary (awaiting triage)
 *   under_review  -> warning (someone is on it)
 *   action_issued -> primary
 *   resolved      -> success
 *   dismissed     -> neutral
 */
export function HazardStatusBadge({ status }: HazardStatusBadgeProps) {
  const { t } = useTranslation();
  const variant = (() => {
    switch (status) {
      case 'resolved':
        return 'success' as const;
      case 'submitted':
      case 'action_issued':
        return 'primary' as const;
      case 'under_review':
        return 'warning' as const;
      case 'dismissed':
      default:
        return 'neutral' as const;
    }
  })();
  return (
    <Badge variant={variant}>{t(`status.hazard.${status}`, status.replace(/_/g, ' '))}</Badge>
  );
}

interface TpiStatusBadgeProps {
  /** Backed by latest_certification.is_valid + result. */
  cert: { is_valid: boolean; result: string } | null | undefined;
}

/**
 * Equipment TPI status:
 *   no cert       -> destructive ("No TPI")
 *   is_valid=true -> success or warning if pass_with_conditions
 *   else          -> destructive ("Expired")
 */
export function TpiStatusBadge({ cert }: TpiStatusBadgeProps) {
  const { t } = useTranslation();
  if (!cert) {
    return <Badge variant="destructive">{t('status.tpi.missing', 'No TPI')}</Badge>;
  }
  if (!cert.is_valid) {
    return <Badge variant="destructive">{t('status.tpi.expired', 'TPI expired')}</Badge>;
  }
  if (cert.result === 'pass_with_conditions') {
    return <Badge variant="warning">{t('status.tpi.conditions', 'TPI w/ conditions')}</Badge>;
  }
  return <Badge variant="success">{t('status.tpi.valid', 'TPI valid')}</Badge>;
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
