import { ArrowLeft, CheckCircle2, Send, X, XCircle } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';
import { toast } from 'sonner';

import { ApiError, type PermitValidationDetails } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogPanel,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PermitStatusBadge } from '@/components/shared/StatusBadge';
import {
  useApprovePermit,
  useClosePermit,
  usePermit,
  usePermitEvents,
  useRejectPermit,
  useSubmitPermit,
} from '@/hooks/usePermits';

import { AttachWorkersPanel } from './AttachWorkersPanel';
import { HardBlockDialog } from './HardBlockDialog';

export function PermitDetailPage() {
  const { t } = useTranslation();
  const { id } = useParams<{ id: string }>();
  const [tab, setTab] = useState('overview');
  const [hardBlock, setHardBlock] = useState<PermitValidationDetails | null>(null);
  const [confirm, setConfirm] = useState<'reject' | 'close' | null>(null);
  const [confirmInput, setConfirmInput] = useState('');

  const { data, isLoading, isError, error } = usePermit(id);
  const events = usePermitEvents(id);
  const submit = useSubmitPermit(id ?? '');
  const approve = useApprovePermit(id ?? '');
  const reject = useRejectPermit(id ?? '');
  const close = useClosePermit(id ?? '');

  if (isLoading) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }
  if (isError || !data) {
    return (
      <div className="text-sm text-destructive">
        {(error as Error)?.message ??
          t('permits.detail.error', 'Could not load permit.')}
      </div>
    );
  }

  const permit = data.data;
  const status = permit.status;

  function onSubmitClick() {
    submit.mutate(undefined, {
      onSuccess: () => toast.success(t('permits.actions.submitted', 'Permit submitted.')),
      onError: (err) => {
        if (
          err instanceof ApiError &&
          err.code === 'PERMIT_VALIDATION_FAILED' &&
          err.details
        ) {
          setHardBlock(err.details as unknown as PermitValidationDetails);
        } else if (err instanceof ApiError) {
          toast.error(err.message);
        } else {
          toast.error(t('permits.actions.error', 'Action failed.'));
        }
      },
    });
  }

  function onApproveClick() {
    approve.mutate(undefined, {
      onSuccess: () => toast.success(t('permits.actions.approved', 'Permit approved.')),
      onError: (err) =>
        toast.error(err instanceof ApiError ? err.message : t('permits.actions.error', 'Action failed.')),
    });
  }

  function onConfirm() {
    const value = confirmInput.trim();
    if (confirm === 'reject') {
      if (value.length < 5) return;
      reject.mutate(value, {
        onSuccess: () => {
          toast.success(t('permits.actions.rejected', 'Permit rejected.'));
          setConfirm(null);
          setConfirmInput('');
        },
        onError: (err) =>
          toast.error(err instanceof ApiError ? err.message : t('permits.actions.error', 'Action failed.')),
      });
    } else if (confirm === 'close') {
      close.mutate(value || undefined, {
        onSuccess: () => {
          toast.success(t('permits.actions.closed', 'Permit closed.'));
          setConfirm(null);
          setConfirmInput('');
        },
        onError: (err) =>
          toast.error(err instanceof ApiError ? err.message : t('permits.actions.error', 'Action failed.')),
      });
    }
  }

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-start gap-3">
        <Link to="/permits">
          <Button variant="ghost" size="icon" aria-label={t('common.back', 'Back')}>
            <ArrowLeft className="size-4 rtl:rotate-180" />
          </Button>
        </Link>
        <div className="flex-1 min-w-0">
          <div className="flex items-baseline gap-2 flex-wrap">
            <h2 className="text-lg font-medium">
              <span className="mono">{permit.permit_number}</span>
            </h2>
            <PermitStatusBadge status={status} />
            {permit.permit_type && (
              <span className="text-sm text-muted-foreground">
                · {permit.permit_type.name_en}{' '}
                <span className="mono text-[11px]">({permit.permit_type.code})</span>
              </span>
            )}
          </div>
          <p className="text-sm text-muted-foreground mt-0.5 truncate max-w-prose">
            {permit.scope_en}
          </p>
        </div>

        {/* Lifecycle action buttons */}
        <div className="flex items-center gap-2 shrink-0">
          {status === 'draft' && (
            <Button onClick={onSubmitClick} disabled={submit.isPending}>
              <Send className="size-3.5 me-1" />
              {submit.isPending ? t('common.loading', 'Loading…') : t('permits.actions.submit', 'Submit')}
            </Button>
          )}
          {status === 'submitted' && (
            <>
              <Button variant="secondary" onClick={() => setConfirm('reject')}>
                <XCircle className="size-3.5 me-1" />
                {t('permits.actions.reject', 'Reject')}
              </Button>
              <Button onClick={onApproveClick} disabled={approve.isPending}>
                <CheckCircle2 className="size-3.5 me-1" />
                {approve.isPending
                  ? t('common.loading', 'Loading…')
                  : t('permits.actions.approve', 'Approve')}
              </Button>
            </>
          )}
          {status === 'approved' && (
            <Button variant="secondary" onClick={() => setConfirm('close')}>
              <X className="size-3.5 me-1" />
              {t('permits.actions.close', 'Close')}
            </Button>
          )}
        </div>
      </div>

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList>
          <TabsTrigger value="overview">{t('permit_detail.tabs.overview', 'Overview')}</TabsTrigger>
          <TabsTrigger value="events">{t('permit_detail.tabs.events', 'Lifecycle')}</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <Card>
              <CardHeader>
                <CardTitle>{t('permit_detail.scope', 'Scope')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm">
                <p>{permit.scope_en}</p>
                {permit.scope_ar && (
                  <p className="text-muted-foreground">{permit.scope_ar}</p>
                )}
                {permit.location_description_en && (
                  <p className="text-muted-foreground">
                    {t('permit_detail.location', 'Location')}: {permit.location_description_en}
                  </p>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>{t('permit_detail.lifecycle_dates', 'Lifecycle')}</CardTitle>
              </CardHeader>
              <CardContent>
                <dl className="space-y-2 text-sm">
                  <Row
                    label={t('permit_detail.valid_from', 'Valid from')}
                    value={permit.valid_from?.replace('T', ' ').slice(0, 16) ?? '—'}
                    mono
                  />
                  <Row
                    label={t('permit_detail.valid_until', 'Valid until')}
                    value={permit.valid_until?.replace('T', ' ').slice(0, 16) ?? '—'}
                    mono
                  />
                  <Row
                    label={t('permit_detail.submitted_at', 'Submitted')}
                    value={permit.submitted_at?.replace('T', ' ').slice(0, 16) ?? '—'}
                    mono
                  />
                  <Row
                    label={t('permit_detail.approved_at', 'Approved')}
                    value={permit.approved_at?.replace('T', ' ').slice(0, 16) ?? '—'}
                    mono
                  />
                  {permit.rejected_at && (
                    <Row
                      label={t('permit_detail.rejected_at', 'Rejected')}
                      value={`${permit.rejected_at.replace('T', ' ').slice(0, 16)}${
                        permit.rejection_reason ? ` — ${permit.rejection_reason}` : ''
                      }`}
                    />
                  )}
                  <Row
                    label={t('permit_detail.closed_at', 'Closed')}
                    value={permit.closed_at?.replace('T', ' ').slice(0, 16) ?? '—'}
                    mono
                  />
                </dl>
              </CardContent>
            </Card>

            <Card className="lg:col-span-2">
              <CardHeader>
                <CardTitle>{t('permit_detail.workers_summary', 'Named workers')}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground">
                  {t('permit_detail.workers_count', '{{count}} attached.', {
                    count: permit.workers_count ?? 0,
                  })}
                </p>
              </CardContent>
            </Card>
          </div>

          {status === 'draft' && id && (
            <div className="mt-4">
              <AttachWorkersPanel permitId={id} />
            </div>
          )}
        </TabsContent>

        <TabsContent value="events">
          <Card>
            <CardContent className="p-0">
              {events.isLoading ? (
                <p className="text-sm text-muted-foreground px-4 py-6">
                  {t('common.loading', 'Loading…')}
                </p>
              ) : events.data?.data.length === 0 ? (
                <p className="text-sm text-muted-foreground px-4 py-6">
                  {t('permit_detail.events_empty', 'No events recorded yet.')}
                </p>
              ) : (
                <ol className="divide-y divide-border">
                  {events.data?.data.map((e) => (
                    <li key={e.id} className="px-4 py-3 text-sm flex items-start gap-4">
                      <span className="mono tabular-nums text-xs text-muted-foreground shrink-0 w-40">
                        {e.occurred_at?.replace('T', ' ').slice(0, 19) ?? ''}
                      </span>
                      <div className="min-w-0">
                        <p className="mono text-xs uppercase tracking-wide text-foreground/80">
                          {e.event_type}
                        </p>
                        {e.comment && (
                          <p className="mt-0.5 text-foreground/80">{e.comment}</p>
                        )}
                      </div>
                    </li>
                  ))}
                </ol>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Reject / Close prompt */}
      <Dialog
        open={confirm !== null}
        onOpenChange={(o) => !o && (setConfirm(null), setConfirmInput(''))}
      >
        <DialogPanel className="max-w-md">
          <DialogHeader onClose={() => (setConfirm(null), setConfirmInput(''))}>
            <DialogTitle>
              {confirm === 'reject'
                ? t('permits.confirm.reject_title', 'Reject permit')
                : t('permits.confirm.close_title', 'Close permit')}
            </DialogTitle>
            <DialogDescription>
              {confirm === 'reject'
                ? t(
                    'permits.confirm.reject_subtitle',
                    'Provide a reason. The submitter will see this on the permit.'
                  )
                : t(
                    'permits.confirm.close_subtitle',
                    'Optional closure notes — what work was completed, any incidents, etc.'
                  )}
            </DialogDescription>
          </DialogHeader>
          <DialogContent>
            <Input
              autoFocus
              placeholder={
                confirm === 'reject'
                  ? t('permits.confirm.reject_placeholder', 'Reason for rejection (≥5 chars)')
                  : t('permits.confirm.close_placeholder', 'Closure notes (optional)')
              }
              value={confirmInput}
              onChange={(e) => setConfirmInput(e.target.value)}
            />
          </DialogContent>
          <DialogFooter>
            <Button
              variant="secondary"
              onClick={() => (setConfirm(null), setConfirmInput(''))}
            >
              {t('common.cancel', 'Cancel')}
            </Button>
            <Button
              variant={confirm === 'reject' ? 'destructive' : 'primary'}
              onClick={onConfirm}
              disabled={
                confirm === 'reject'
                  ? confirmInput.trim().length < 5 || reject.isPending
                  : close.isPending
              }
            >
              {confirm === 'reject'
                ? t('permits.actions.reject', 'Reject')
                : t('permits.actions.close', 'Close')}
            </Button>
          </DialogFooter>
        </DialogPanel>
      </Dialog>

      {/* Hard-block — the demo's marquee error */}
      <HardBlockDialog
        open={hardBlock !== null}
        onClose={() => setHardBlock(null)}
        details={hardBlock}
      />
    </div>
  );
}

function Row({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div className="grid grid-cols-[140px_1fr] gap-3">
      <dt className="text-muted-foreground text-xs">{label}</dt>
      <dd className={mono ? 'mono tabular-nums' : ''}>{value}</dd>
    </div>
  );
}
