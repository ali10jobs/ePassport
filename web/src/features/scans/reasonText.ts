import type { TFunction } from 'i18next';

import type { ScanReason } from '@/api/types';

/**
 * Translates a backend ScanReason into a human-readable line.
 *
 * Stable codes -> i18n keys. Each reason carries optional details
 * (e.g. CERT_EXPIRED.expired_on, CERT_EXPIRED.certification_type) that
 * we splice into the localized message via interpolation.
 */
export function describeReason(reason: ScanReason, t: TFunction): string {
  const details = (reason.details ?? {}) as Record<string, unknown>;
  const code = reason.code as string;

  switch (code) {
    case 'CERT_EXPIRED':
      return t('scan.reason.cert_expired', {
        defaultValue: 'Certification {{cert}} expired on {{expired_on}}',
        cert: (details.certification_type as string) ?? '',
        expired_on: (details.expired_on as string) ?? '',
      });
    case 'CERT_MISSING':
      return t('scan.reason.cert_missing', {
        defaultValue: 'Required certification missing: {{cert}}',
        cert: (details.certification_type as string) ?? '',
      });
    case 'INDUCTION_MISSING':
      return t('scan.reason.induction_missing', {
        defaultValue: 'Site induction is missing or expired (status: {{status}})',
        status: (details.status as string) ?? '',
      });
    case 'MEDICAL_FAIL':
      return t('scan.reason.medical_fail', {
        defaultValue: 'Medical fitness not on file or expired',
      });
    case 'ORG_NOT_ENGAGED':
      return t('scan.reason.org_not_engaged', {
        defaultValue: "Worker's employer is not engaged on this project",
      });
    case 'IMPERSONATION_FLAG':
      return t('scan.reason.impersonation_flag', {
        defaultValue: 'Helmet and coverall belong to different workers',
      });
    case 'EQUIPMENT_TPI_EXPIRED':
      return t('scan.reason.equipment_tpi_expired', {
        defaultValue: 'Equipment TPI inspection expired on {{expiry_date}}',
        expiry_date: (details.expiry_date as string) ?? '',
      });
    case 'OPERATOR_NOT_AUTHORIZED':
      return t('scan.reason.operator_not_authorized', {
        defaultValue: 'Operator is not authorised for this equipment',
      });
    case 'UNKNOWN_QR':
      return t('scan.reason.unknown_qr', {
        defaultValue: 'QR code is not registered',
      });
    default:
      return code;
  }
}
