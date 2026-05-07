import { ShieldAlert, Wrench } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import type { PermitValidationDetails } from '@/api/types';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogPanel,
  DialogTitle,
} from '@/components/ui/dialog';
import { describeReason } from '@/features/scans/reasonText';

interface HardBlockDialogProps {
  open: boolean;
  onClose: () => void;
  details: PermitValidationDetails | null;
}

/**
 * The marquee "which worker, which cert" error moment.
 *
 * When POST /permits/:id/submit returns 422 PERMIT_VALIDATION_FAILED, the
 * Permit detail page surfaces this dialog, which lists every named
 * worker who failed validation with their specific reasons + every
 * piece of equipment with a TPI failure + project-level failures.
 *
 * Each worker row links back to the worker passport so the operator
 * can fix the cert and resubmit without losing context.
 */
export function HardBlockDialog({ open, onClose, details }: HardBlockDialogProps) {
  const { t } = useTranslation();

  return (
    <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
      <DialogPanel className="max-w-2xl">
        <DialogHeader onClose={onClose}>
          <div className="flex items-start gap-3">
            <div className="size-9 shrink-0 rounded-md bg-destructive/10 grid place-items-center text-destructive">
              <ShieldAlert className="size-5" strokeWidth={2.25} />
            </div>
            <div className="min-w-0">
              <DialogTitle>{t('permits.hardblock.title', 'Permit cannot be submitted')}</DialogTitle>
              <DialogDescription>
                {t(
                  'permits.hardblock.subtitle',
                  'Each named worker and piece of equipment was re-validated. Fix the issues below and resubmit.'
                )}
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <DialogContent className="space-y-5">
          {/* Project-level failures */}
          {details?.project_failures?.length ? (
            <section>
              <h3 className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground mb-2">
                {t('permits.hardblock.project_issues', 'Permit-level issues')}
              </h3>
              <ul className="space-y-1.5">
                {details.project_failures.map((p, i) => (
                  <li
                    key={`${p.code}-${i}`}
                    className="rounded-md border border-destructive/30 bg-destructive/5 px-3 py-2 text-sm"
                  >
                    <p className="mono text-[11px] uppercase tracking-wide text-destructive">
                      {p.code}
                    </p>
                    <p className="mt-0.5 text-foreground">
                      {describeReason({ code: p.code, details: p.details ?? null }, t)}
                    </p>
                  </li>
                ))}
              </ul>
            </section>
          ) : null}

          {/* Worker failures — the main demo content */}
          {details?.worker_failures?.length ? (
            <section>
              <h3 className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground mb-2">
                {t('permits.hardblock.worker_issues', 'Worker issues')}{' '}
                <span className="mono tabular-nums text-foreground/60">
                  ({details.worker_failures.length})
                </span>
              </h3>
              <ul className="space-y-3">
                {details.worker_failures.map((wf) => (
                  <li
                    key={wf.worker_id}
                    className="rounded-md border border-border overflow-hidden"
                  >
                    <div className="flex items-center justify-between gap-3 px-3 py-2 bg-muted/40 border-b border-border">
                      <div className="min-w-0">
                        <div className="flex items-baseline gap-2">
                          <span className="mono text-xs">{wf.employee_id}</span>
                          <span className="font-medium text-sm truncate">
                            {wf.full_name_en}
                          </span>
                        </div>
                        <div className="text-xs text-muted-foreground mt-0.5">
                          {t('permits.hardblock.role_on_permit', 'role')}:{' '}
                          <span className="mono">{wf.role_on_permit}</span>
                        </div>
                      </div>
                      <Link to={`/workers/${wf.worker_id}`}>
                        <Button variant="secondary" size="sm" onClick={onClose}>
                          {t('permits.hardblock.open_worker', 'Open worker')}
                        </Button>
                      </Link>
                    </div>
                    <ul className="divide-y divide-border">
                      {wf.reasons.map((r, i) => (
                        <li key={`${r.code}-${i}`} className="px-3 py-2 text-sm">
                          <div className="flex items-baseline gap-2">
                            <span className="mono text-[11px] uppercase tracking-wide text-destructive">
                              {r.code}
                            </span>
                          </div>
                          <p className="mt-0.5 text-foreground">{describeReason(r, t)}</p>
                        </li>
                      ))}
                    </ul>
                  </li>
                ))}
              </ul>
            </section>
          ) : null}

          {/* Equipment failures */}
          {details?.equipment_failures?.length ? (
            <section>
              <h3 className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground mb-2">
                {t('permits.hardblock.equipment_issues', 'Equipment issues')}{' '}
                <span className="mono tabular-nums text-foreground/60">
                  ({details.equipment_failures.length})
                </span>
              </h3>
              <ul className="space-y-3">
                {details.equipment_failures.map((ef) => (
                  <li
                    key={ef.equipment_id}
                    className="rounded-md border border-border overflow-hidden"
                  >
                    <div className="flex items-center gap-2 px-3 py-2 bg-muted/40 border-b border-border">
                      <Wrench className="size-3.5 text-muted-foreground" />
                      <div className="flex-1 min-w-0">
                        <div className="flex items-baseline gap-2">
                          <span className="mono text-xs">{ef.asset_tag}</span>
                          <span className="text-sm truncate">
                            {ef.manufacturer ?? ''} {ef.model ?? ''}
                          </span>
                        </div>
                      </div>
                    </div>
                    <ul className="divide-y divide-border">
                      {ef.reasons.map((r, i) => (
                        <li key={`${r.code}-${i}`} className="px-3 py-2 text-sm">
                          <span className="mono text-[11px] uppercase tracking-wide text-destructive">
                            {r.code}
                          </span>
                          <p className="mt-0.5 text-foreground">{describeReason(r, t)}</p>
                        </li>
                      ))}
                    </ul>
                  </li>
                ))}
              </ul>
            </section>
          ) : null}
        </DialogContent>

        <DialogFooter>
          <Button variant="secondary" onClick={onClose}>
            {t('common.close', 'Close')}
          </Button>
        </DialogFooter>
      </DialogPanel>
    </Dialog>
  );
}
