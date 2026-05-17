import { ArrowLeft, Printer } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';

import type { WorkerPassport } from '@/api/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { CertStatusBadge, InductionStatusBadge } from '@/components/shared/StatusBadge';
import { useWorkerHelmetQr, useWorkerPassport } from '@/hooks/useWorkers';

export function WorkerDetailPage() {
  const { t } = useTranslation();
  const { id } = useParams<{ id: string }>();
  const [tab, setTab] = useState('overview');
  const { data, isLoading, isError, error } = useWorkerPassport(id);

  if (isLoading) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }
  if (isError || !data) {
    return (
      <div className="text-sm text-destructive">
        {(error as Error)?.message ?? t('workers.detail.error', 'Could not load worker.')}
      </div>
    );
  }

  const worker = data.data;

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <Link to="/workers">
          <Button variant="ghost" size="icon" aria-label={t('common.back', 'Back')}>
            <ArrowLeft className="size-4 rtl:rotate-180" />
          </Button>
        </Link>
        <h2 className="text-sm text-muted-foreground">
          {t('workers.detail.crumb', 'Worker profile')}
        </h2>
      </div>

      <ProfileHeader worker={worker} />

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList>
          <TabsTrigger value="overview">{t('worker_detail.tabs.overview', 'Overview')}</TabsTrigger>
          <TabsTrigger value="certifications">
            {t('worker_detail.tabs.certifications', 'Certifications')}
          </TabsTrigger>
          <TabsTrigger value="medical">{t('worker_detail.tabs.medical', 'Medical')}</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <Card className="lg:col-span-1">
              <CardHeader>
                <CardTitle>{t('worker_detail.identity', 'Identity')}</CardTitle>
              </CardHeader>
              <CardContent>
                <dl className="space-y-2 text-sm">
                  <Field
                    label={t('worker_detail.full_name_ar', 'Name (Arabic)')}
                    value={worker.full_name_ar || '—'}
                  />
                  <Field
                    label={t('worker_detail.nationality', 'Nationality')}
                    value={worker.nationality ?? '—'}
                    mono
                  />
                  <Field
                    label={t('worker_detail.employer', 'Employer')}
                    value={worker.employer.name_en ?? '—'}
                  />
                </dl>
              </CardContent>
            </Card>

            <Card className="lg:col-span-1">
              <CardHeader>
                <CardTitle>{t('worker_detail.induction', 'Induction')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <InductionStatusBadge status={worker.induction.status} />
                <dl className="space-y-2 text-sm">
                  <Field
                    label={t('worker_detail.induction_date', 'Induction date')}
                    value={worker.induction.date ?? '—'}
                    mono
                  />
                  <Field
                    label={t('worker_detail.induction_valid_until', 'Valid until')}
                    value={worker.induction.valid_until ?? '—'}
                    mono
                  />
                </dl>
              </CardContent>
            </Card>

            <Card className="lg:col-span-1">
              <CardHeader>
                <CardTitle>{t('worker_detail.medical', 'Medical fitness')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {worker.medical_fitness ? (
                  <>
                    <Badge
                      variant={
                        worker.medical_fitness.is_currently_fit ? 'success' : 'destructive'
                      }
                    >
                      {worker.medical_fitness.status.replace(/_/g, ' ')}
                    </Badge>
                    <dl className="space-y-2 text-sm">
                      <Field
                        label={t('worker_detail.medical_exam_date', 'Last exam')}
                        value={worker.medical_fitness.exam_date ?? '—'}
                        mono
                      />
                      <Field
                        label={t('worker_detail.medical_valid_until', 'Valid until')}
                        value={worker.medical_fitness.valid_until ?? '—'}
                        mono
                      />
                    </dl>
                  </>
                ) : (
                  <p className="text-sm text-muted-foreground">
                    {t('worker_detail.medical_none', 'No medical record on file.')}
                  </p>
                )}
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="certifications">
          <Card>
            {worker.certifications.length === 0 ? (
              <CardContent className="text-sm text-muted-foreground py-12 text-center">
                {t('worker_detail.certs_empty', 'No certifications on file.')}
              </CardContent>
            ) : (
              <Table>
                <THead>
                  <TR>
                    <TH>{t('worker_detail.col_cert', 'Certification')}</TH>
                    <TH>{t('worker_detail.col_issuer', 'Issuer')}</TH>
                    <TH>{t('worker_detail.col_issue_date', 'Issued')}</TH>
                    <TH>{t('worker_detail.col_expiry', 'Expiry')}</TH>
                    <TH>{t('worker_detail.col_status', 'Status')}</TH>
                  </TR>
                </THead>
                <TBody>
                  {worker.certifications.map((c) => (
                    <TR key={c.id}>
                      <TD>
                        <div className="flex flex-col gap-0.5">
                          <span>{c.type_name_en ?? c.type_code ?? '—'}</span>
                          <span className="mono text-[11px] text-muted-foreground">
                            {c.type_code ?? ''}
                          </span>
                        </div>
                      </TD>
                      <TD className="text-muted-foreground">{c.issuing_body_en ?? '—'}</TD>
                      <TD className="mono tabular-nums text-muted-foreground">
                        {c.issue_date ?? '—'}
                      </TD>
                      <TD className="mono tabular-nums">
                        {c.expiry_date ?? <span className="text-muted-foreground">—</span>}
                      </TD>
                      <TD>
                        <CertStatusBadge status={c.status} />
                      </TD>
                    </TR>
                  ))}
                </TBody>
              </Table>
            )}
          </Card>
        </TabsContent>

        <TabsContent value="medical">
          <Card>
            {worker.medical_fitness ? (
              <Table>
                <THead>
                  <TR>
                    <TH>{t('worker_detail.medical_status', 'Status')}</TH>
                    <TH>{t('worker_detail.medical_currently_fit', 'Currently fit')}</TH>
                    <TH>{t('worker_detail.medical_exam_date', 'Last exam')}</TH>
                    <TH>{t('worker_detail.medical_valid_until', 'Valid until')}</TH>
                  </TR>
                </THead>
                <TBody>
                  <TR>
                    <TD className="capitalize">
                      {worker.medical_fitness.status.replace(/_/g, ' ')}
                    </TD>
                    <TD>{worker.medical_fitness.is_currently_fit ? 'Yes' : 'No'}</TD>
                    <TD className="mono tabular-nums">
                      {worker.medical_fitness.exam_date ?? '—'}
                    </TD>
                    <TD className="mono tabular-nums">
                      {worker.medical_fitness.valid_until ?? '—'}
                    </TD>
                  </TR>
                </TBody>
              </Table>
            ) : (
              <CardContent className="text-sm text-muted-foreground py-12 text-center">
                {t('worker_detail.medical_none', 'No medical record on file.')}
              </CardContent>
            )}
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}

function Field({
  label,
  value,
  mono,
}: {
  label: string;
  value: string;
  mono?: boolean;
}) {
  return (
    <div className="grid grid-cols-[120px_1fr] gap-3">
      <dt className="text-muted-foreground text-xs">{label}</dt>
      <dd className={mono ? 'mono tabular-nums' : ''}>{value}</dd>
    </div>
  );
}

/**
 * Hero card at the top of the worker detail screen: avatar on the left,
 * identity and status chips in the middle, helmet QR + print on the right.
 *
 * The QR PNG is fetched as a blob (the endpoint is authenticated, so we
 * can't use a plain <img src>). The Print button opens a tiny print-only
 * window containing the QR + worker name + employee ID so the operator
 * can stick it on a helmet.
 */
function ProfileHeader({ worker }: { worker: WorkerPassport }) {
  const { t, i18n } = useTranslation();
  const isArabic = i18n.language.startsWith('ar');
  const { data: qrBlob, isLoading: qrLoading, isError: qrError } = useWorkerHelmetQr(worker.id);

  const qrUrl = useMemo(
    () => (qrBlob ? URL.createObjectURL(qrBlob) : null),
    [qrBlob]
  );
  useEffect(() => {
    return () => {
      if (qrUrl) URL.revokeObjectURL(qrUrl);
    };
  }, [qrUrl]);

  const initials = (worker.full_name_en || '?')
    .split(/\s+/)
    .map((w) => w[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();

  const handlePrint = () => {
    if (!qrUrl) return;
    const w = window.open('', '_blank', 'width=420,height=560');
    if (!w) return;
    w.document.write(`<!doctype html>
<html>
  <head>
    <title>${escapeHtml(worker.full_name_en)} — Helmet QR</title>
    <style>
      *{box-sizing:border-box}
      body{font-family:system-ui,-apple-system,sans-serif;margin:0;padding:32px;text-align:center;color:#111}
      h1{font-size:18px;margin:0 0 4px}
      p{font-size:12px;color:#555;margin:0 0 16px}
      .id{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;color:#555;margin-top:8px}
      img{width:280px;height:280px;image-rendering:pixelated}
      @media print { @page { margin: 12mm; } body{padding:0} }
    </style>
  </head>
  <body>
    <h1>${escapeHtml(worker.full_name_en)}</h1>
    <p>${escapeHtml(worker.employer.name_en ?? '')}</p>
    <img src="${qrUrl}" alt="Helmet QR" />
    <div class="id">${escapeHtml(worker.employee_id)}</div>
    <script>window.onload = () => { window.focus(); window.print(); };</script>
  </body>
</html>`);
    w.document.close();
  };

  return (
    <Card>
      <CardContent className="p-6">
        <div className="flex flex-col md:flex-row gap-6 items-start">
          <Avatar photoPath={worker.photo_path} initials={initials} />

          <div className="flex-1 min-w-0 space-y-2">
            <div className="flex flex-wrap items-baseline gap-x-3 gap-y-1">
              <h1 className="text-2xl font-semibold leading-tight truncate">
                {isArabic && worker.full_name_ar ? worker.full_name_ar : worker.full_name_en}
              </h1>
              <span className="mono text-xs text-muted-foreground">
                {worker.employee_id}
              </span>
            </div>
            <p className="text-sm text-muted-foreground">
              {worker.trade ?? t('worker_detail.no_trade', 'No trade')} ·{' '}
              {worker.employer.name_en ?? '—'}
            </p>
            <div className="flex flex-wrap gap-2 pt-2">
              <InductionStatusBadge status={worker.induction.status} />
              {worker.medical_fitness && (
                <Badge
                  variant={worker.medical_fitness.is_currently_fit ? 'success' : 'destructive'}
                >
                  {t('worker_detail.medical', 'Medical fitness')}:{' '}
                  {worker.medical_fitness.status.replace(/_/g, ' ')}
                </Badge>
              )}
              {worker.nationality && (
                <Badge variant="neutral" className="mono uppercase">
                  {worker.nationality}
                </Badge>
              )}
            </div>
          </div>

          <div className="flex flex-col items-center gap-2 shrink-0">
            <div className="size-36 rounded-md border border-border bg-white flex items-center justify-center overflow-hidden">
              {qrLoading && (
                <span className="text-xs text-muted-foreground">
                  {t('common.loading', 'Loading…')}
                </span>
              )}
              {qrError && (
                <span className="text-xs text-destructive text-center px-2">
                  {t('worker_detail.qr_error', 'QR unavailable')}
                </span>
              )}
              {qrUrl && (
                <img src={qrUrl} alt={t('worker_detail.qr_alt', 'Helmet QR')} className="size-full object-contain" />
              )}
            </div>
            <Button
              size="sm"
              variant="secondary"
              onClick={handlePrint}
              disabled={!qrUrl}
              className="w-full"
            >
              <Printer className="size-3.5 mr-1.5" />
              {t('worker_detail.print_qr', 'Print QR')}
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

function Avatar({ photoPath, initials }: { photoPath: string | null; initials: string }) {
  if (photoPath) {
    return (
      <img
        src={photoPath}
        alt=""
        className="size-24 rounded-full object-cover border border-border shrink-0"
      />
    );
  }
  return (
    <div className="size-24 rounded-full bg-muted text-muted-foreground flex items-center justify-center text-2xl font-medium shrink-0">
      {initials}
    </div>
  );
}

function escapeHtml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
