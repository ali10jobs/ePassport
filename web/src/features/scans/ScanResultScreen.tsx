import { AlertOctagon, CheckCircle2, ScanSearch, XCircle } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import type { ScanResult } from '@/api/types';
import { Button } from '@/components/ui/button';
import { useWorkerPassport } from '@/hooks/useWorkers';

import { describeReason } from './reasonText';

interface ScanResultScreenProps {
  result: ScanResult;
  onDismiss: () => void;
}

/**
 * Full-bleed result screen — the demo's marquee visual.
 *
 *   GREEN: solid emerald background, worker identity, valid certs.
 *   RED:   solid red, mono reason codes + per-reason details.
 *   IMPERSONATION_FLAG: amber-edged red, dedicated banner.
 *
 * Subject identity is fetched on demand if the scan resolved to a
 * worker, so the screen always shows a name + employee_id rather than
 * just an opaque UUID.
 */
export function ScanResultScreen({ result, onDismiss }: ScanResultScreenProps) {
  const { t } = useTranslation();

  const isWorker = result.subject_type === 'worker' && !!result.subject_id;
  const passportQuery = useWorkerPassport(isWorker ? result.subject_id! : undefined);
  const subject = passportQuery.data?.data ?? null;

  const variant: 'green' | 'red' | 'impersonation' =
    result.result === 'green'
      ? 'green'
      : result.result === 'impersonation_flag'
        ? 'impersonation'
        : 'red';

  const surfaces: Record<typeof variant, string> = {
    green: 'bg-emerald-600 text-white',
    red: 'bg-red-600 text-white',
    impersonation: 'bg-red-700 text-white ring-4 ring-amber-400/70 ring-inset',
  };

  return (
    <div
      className={`fixed inset-0 z-50 flex flex-col ${surfaces[variant]}`}
      role="alert"
      aria-live="assertive"
    >
      {/* Header band */}
      <div className="flex items-center gap-3 px-6 pt-8">
        {variant === 'green' && <CheckCircle2 className="size-10 shrink-0" strokeWidth={2.25} />}
        {variant === 'red' && <XCircle className="size-10 shrink-0" strokeWidth={2.25} />}
        {variant === 'impersonation' && (
          <AlertOctagon className="size-10 shrink-0" strokeWidth={2.25} />
        )}
        <div className="min-w-0">
          <h1 className="text-3xl font-semibold uppercase tracking-tight leading-none">
            {variant === 'green' && t('scan.result.green', 'GO')}
            {variant === 'red' && t('scan.result.red', 'STOP')}
            {variant === 'impersonation' && t('scan.result.impersonation', 'IMPERSONATION')}
          </h1>
          <p className="mt-1 text-sm text-white/80">
            {variant === 'green'
              ? t('scan.result.green_subtitle', 'Authorised — proceed.')
              : variant === 'impersonation'
                ? t(
                    'scan.result.impersonation_subtitle',
                    'Helmet and coverall belong to different workers.'
                  )
                : t('scan.result.red_subtitle', 'Not authorised. See reasons below.')}
          </p>
        </div>
      </div>

      {/* Subject identity */}
      <div className="flex-1 px-6 pt-6 overflow-y-auto">
        {isWorker && (
          <div className="mb-6">
            <p className="text-xs uppercase tracking-wide text-white/70">
              {t('scan.subject_worker', 'Worker')}
            </p>
            {subject ? (
              <div className="mt-1">
                <p className="text-2xl font-medium leading-tight">{subject.full_name_en}</p>
                {subject.full_name_ar && (
                  <p className="text-lg text-white/85 leading-tight">{subject.full_name_ar}</p>
                )}
                <div className="mt-2 flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm text-white/80">
                  <span className="mono">{subject.employee_id}</span>
                  {subject.trade && <span>• {subject.trade}</span>}
                  {subject.employer.name_en && (
                    <span>• {subject.employer.name_en}</span>
                  )}
                </div>
              </div>
            ) : (
              <p className="mt-1 text-white/70 text-sm">{t('common.loading', 'Loading…')}</p>
            )}
          </div>
        )}

        {result.subject_type === 'equipment' && (
          <div className="mb-6">
            <p className="text-xs uppercase tracking-wide text-white/70">
              {t('scan.subject_equipment', 'Equipment')}
            </p>
            <p className="mt-1 mono text-base">{result.subject_id ?? '—'}</p>
          </div>
        )}

        {/* Reasons (only on RED / IMPERSONATION) */}
        {result.reasons.length > 0 && (
          <div>
            <p className="text-xs uppercase tracking-wide text-white/70">
              {t('scan.reasons', 'Reasons')}
            </p>
            <ul className="mt-2 space-y-2">
              {result.reasons.map((r, i) => (
                <li
                  key={`${r.code}-${i}`}
                  className="bg-white/10 backdrop-blur-sm rounded-md border border-white/20 px-3 py-2"
                >
                  <p className="mono text-xs uppercase tracking-wide text-white/85">{r.code}</p>
                  <p className="mt-0.5 text-sm">{describeReason(r, t)}</p>
                </li>
              ))}
            </ul>
          </div>
        )}

        {variant === 'green' && subject && (
          <div className="mt-2 text-sm text-white/80">
            <p className="text-xs uppercase tracking-wide text-white/70">
              {t('scan.green.cert_summary', 'Certifications on file')}
            </p>
            <p className="mt-1 mono tabular-nums">
              {subject.certifications.length}
            </p>
          </div>
        )}
      </div>

      {/* Footer with re-scan button */}
      <div className="px-6 pb-8 pt-4">
        <Button
          size="lg"
          variant="secondary"
          onClick={onDismiss}
          className="w-full bg-white text-black hover:bg-white/90 border-transparent h-12 text-base font-medium"
        >
          <ScanSearch className="size-5 me-2" />
          {t('scan.scan_another', 'Scan another')}
        </Button>
        <p className="mt-3 text-center text-[11px] text-white/60 mono">
          {t('scan.event_id', 'event_id')}: {result.event_id}
        </p>
      </div>
    </div>
  );
}
