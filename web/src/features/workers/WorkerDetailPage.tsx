import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { CertStatusBadge, InductionStatusBadge } from '@/components/shared/StatusBadge';
import { useWorkerPassport } from '@/hooks/useWorkers';

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
        <div className="min-w-0">
          <div className="flex items-baseline gap-2">
            <h2 className="text-lg font-medium truncate">{worker.full_name_en}</h2>
            <span className="mono text-xs text-muted-foreground">{worker.employee_id}</span>
          </div>
          <p className="text-sm text-muted-foreground">
            {worker.trade ?? '—'} · {worker.employer.name_en ?? '—'}
          </p>
        </div>
      </div>

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
            <CardContent className="space-y-3">
              {worker.medical_fitness ? (
                <dl className="grid grid-cols-2 gap-3 text-sm">
                  <Field
                    label={t('worker_detail.medical_status', 'Status')}
                    value={worker.medical_fitness.status.replace(/_/g, ' ')}
                  />
                  <Field
                    label={t('worker_detail.medical_currently_fit', 'Currently fit')}
                    value={worker.medical_fitness.is_currently_fit ? 'Yes' : 'No'}
                  />
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
              ) : (
                <p className="text-sm text-muted-foreground">
                  {t('worker_detail.medical_none', 'No medical record on file.')}
                </p>
              )}
            </CardContent>
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
