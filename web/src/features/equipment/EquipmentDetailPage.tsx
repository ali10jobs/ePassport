import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';
import { toast } from 'sonner';

import { ApiError } from '@/api/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { TpiStatusBadge } from '@/components/shared/StatusBadge';
import {
  useAttachEquipmentCertification,
  useEquipment,
} from '@/hooks/useEquipment';

export function EquipmentDetailPage() {
  const { t } = useTranslation();
  const { id } = useParams<{ id: string }>();
  const [attachOpen, setAttachOpen] = useState(false);
  const { data, isLoading, isError, error } = useEquipment(id);

  if (isLoading) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }
  if (isError || !data) {
    return (
      <div className="text-sm text-destructive">
        {(error as Error)?.message ?? t('equipment.detail.error', 'Could not load equipment.')}
      </div>
    );
  }

  const eq = data.data;
  const cert = eq.latest_certification ?? null;

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <Link to="/equipment">
          <Button variant="ghost" size="icon" aria-label={t('common.back', 'Back')}>
            <ArrowLeft className="size-4 rtl:rotate-180" />
          </Button>
        </Link>
        <div className="min-w-0 flex-1">
          <div className="flex items-baseline gap-2 flex-wrap">
            <h2 className="text-lg font-medium truncate">
              {eq.manufacturer || eq.model
                ? [eq.manufacturer, eq.model].filter(Boolean).join(' ')
                : t('equipment.detail.untitled', 'Equipment')}
            </h2>
            <span className="mono text-xs text-muted-foreground">{eq.asset_tag}</span>
          </div>
          <p className="text-sm text-muted-foreground">
            {t(`equipment.type.${eq.type}`, eq.type)}
            {' · '}
            {eq.owner_organization?.name_en ?? '—'}
          </p>
        </div>
        <Button size="sm" onClick={() => setAttachOpen(true)}>
          {t('equipment.detail.attach_tpi', 'Attach TPI')}
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <Card className="lg:col-span-1">
          <CardHeader>
            <CardTitle>{t('equipment.detail.identity', 'Identity')}</CardTitle>
          </CardHeader>
          <CardContent>
            <dl className="space-y-2 text-sm">
              <Field
                label={t('equipment.detail.asset_tag', 'Asset tag')}
                value={eq.asset_tag}
                mono
              />
              <Field
                label={t('equipment.detail.serial', 'Serial number')}
                value={eq.serial_number ?? '—'}
                mono
              />
              <Field
                label={t('equipment.detail.manufacturer', 'Manufacturer')}
                value={eq.manufacturer ?? '—'}
              />
              <Field label={t('equipment.detail.model', 'Model')} value={eq.model ?? '—'} />
              <Field
                label={t('equipment.detail.type', 'Type')}
                value={t(`equipment.type.${eq.type}`, eq.type)}
              />
              {eq.category && (
                <Field label={t('equipment.detail.category', 'Category')} value={eq.category} />
              )}
              <Field
                label={t('equipment.detail.swl', 'Safe working load')}
                value={eq.safe_working_load_kg ? `${eq.safe_working_load_kg} kg` : '—'}
                mono
              />
              <Field
                label={t('equipment.detail.manufactured', 'Manufactured')}
                value={eq.manufacture_date ?? '—'}
                mono
              />
            </dl>
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader>
            <div className="flex items-center justify-between gap-3">
              <CardTitle>{t('equipment.detail.tpi_title', 'Latest TPI inspection')}</CardTitle>
              <TpiStatusBadge cert={cert} />
            </div>
          </CardHeader>
          <CardContent>
            {cert ? (
              <dl className="grid grid-cols-[160px_1fr] gap-y-2 text-sm">
                <dt className="text-muted-foreground text-xs">
                  {t('equipment.detail.tpi_body', 'Inspection body')}
                </dt>
                <dd>{cert.tpi_body_en ?? '—'}</dd>
                <dt className="text-muted-foreground text-xs">
                  {t('equipment.detail.tpi_inspection_date', 'Inspection date')}
                </dt>
                <dd className="mono tabular-nums">{cert.inspection_date ?? '—'}</dd>
                <dt className="text-muted-foreground text-xs">
                  {t('equipment.detail.tpi_expiry', 'Expires')}
                </dt>
                <dd className="mono tabular-nums">{cert.expiry_date ?? '—'}</dd>
                <dt className="text-muted-foreground text-xs">
                  {t('equipment.detail.tpi_result', 'Result')}
                </dt>
                <dd>
                  <Badge variant={cert.result === 'fail' ? 'destructive' : 'outline'}>
                    {t(`status.tpi_result.${cert.result}`, cert.result)}
                  </Badge>
                </dd>
              </dl>
            ) : (
              <div className="text-sm text-muted-foreground py-6 text-center">
                {t(
                  'equipment.detail.tpi_empty',
                  'No TPI inspection on record. The gate will hard-block this asset.'
                )}
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {attachOpen && id && (
        <AttachTpiDialog
          equipmentId={id}
          open={attachOpen}
          onClose={() => setAttachOpen(false)}
        />
      )}
    </div>
  );
}

function Field({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div className="grid grid-cols-[140px_1fr] items-baseline gap-2">
      <dt className="text-xs text-muted-foreground">{label}</dt>
      <dd className={mono ? 'mono tabular-nums' : ''}>{value}</dd>
    </div>
  );
}

interface AttachTpiDialogProps {
  equipmentId: string;
  open: boolean;
  onClose: () => void;
}

function AttachTpiDialog({ equipmentId, open, onClose }: AttachTpiDialogProps) {
  const { t } = useTranslation();
  const [tpiBodyEn, setTpiBodyEn] = useState('');
  const [inspectionDate, setInspectionDate] = useState('');
  const [expiryDate, setExpiryDate] = useState('');
  const [result, setResult] = useState<'pass' | 'pass_with_conditions' | 'fail'>('pass');
  const [certificateNumber, setCertificateNumber] = useState('');

  const attach = useAttachEquipmentCertification(equipmentId);

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    attach.mutate(
      {
        tpi_body_en: tpiBodyEn,
        inspection_date: inspectionDate,
        expiry_date: expiryDate,
        result,
        ...(certificateNumber ? { certificate_number: certificateNumber } : {}),
      },
      {
        onSuccess: () => {
          toast.success(t('equipment.detail.tpi_attached', 'TPI inspection recorded.'));
          onClose();
        },
        onError: (err) => {
          const msg =
            err instanceof ApiError
              ? err.message
              : t('equipment.detail.tpi_attach_failed', 'Could not attach TPI.');
          toast.error(msg);
        },
      }
    );
  }

  return (
    <Dialog open={open} onOpenChange={(v) => !v && onClose()}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{t('equipment.detail.attach_tpi', 'Attach TPI')}</DialogTitle>
        </DialogHeader>
        <form onSubmit={onSubmit} className="space-y-3">
          <FormField label={t('equipment.detail.tpi_body', 'Inspection body')}>
            <Input
              required
              value={tpiBodyEn}
              onChange={(e) => setTpiBodyEn(e.target.value)}
              placeholder="SGS / Bureau Veritas / TÜV…"
            />
          </FormField>
          <div className="grid grid-cols-2 gap-3">
            <FormField label={t('equipment.detail.tpi_inspection_date', 'Inspection date')}>
              <Input
                required
                type="date"
                value={inspectionDate}
                onChange={(e) => setInspectionDate(e.target.value)}
              />
            </FormField>
            <FormField label={t('equipment.detail.tpi_expiry', 'Expires')}>
              <Input
                required
                type="date"
                value={expiryDate}
                onChange={(e) => setExpiryDate(e.target.value)}
              />
            </FormField>
          </div>
          <FormField label={t('equipment.detail.tpi_result', 'Result')}>
            <Select
              value={result}
              onChange={(e) =>
                setResult(e.target.value as 'pass' | 'pass_with_conditions' | 'fail')
              }
            >
              <option value="pass">{t('status.tpi_result.pass', 'Pass')}</option>
              <option value="pass_with_conditions">
                {t('status.tpi_result.pass_with_conditions', 'Pass with conditions')}
              </option>
              <option value="fail">{t('status.tpi_result.fail', 'Fail')}</option>
            </Select>
          </FormField>
          <FormField label={t('equipment.detail.tpi_cert_number', 'Certificate number (optional)')}>
            <Input
              value={certificateNumber}
              onChange={(e) => setCertificateNumber(e.target.value)}
              className="mono"
            />
          </FormField>

          <div className="flex items-center justify-end gap-2 pt-2">
            <Button type="button" variant="secondary" onClick={onClose} disabled={attach.isPending}>
              {t('common.cancel', 'Cancel')}
            </Button>
            <Button type="submit" disabled={attach.isPending}>
              {attach.isPending
                ? t('common.saving', 'Saving…')
                : t('equipment.detail.tpi_save', 'Record TPI')}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}

function FormField({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="block space-y-1">
      <span className="text-xs text-muted-foreground">{label}</span>
      {children}
    </label>
  );
}
