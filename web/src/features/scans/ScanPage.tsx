import { Keyboard, ScanLine } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import type { ScanResult } from '@/api/types';
import { ApiError } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useScanVerify } from '@/hooks/useScanVerify';

import { QrScanner } from './QrScanner';
import { ScanResultScreen } from './ScanResultScreen';

type Mode = 'qr' | 'manual';

/**
 * /scans — gate verification page.
 *
 * State machine:
 *   QR mode: camera live -> on decode -> verify mutation -> result overlay
 *   Manual:  employee_id input -> submit -> verify mutation -> result overlay
 *
 * The same de-duplication ref guards against zxing firing twice on the
 * same payload before the result overlay opens.
 */
export function ScanPage() {
  const { t } = useTranslation();
  const [mode, setMode] = useState<Mode>('qr');
  const [manualId, setManualId] = useState('');
  const [result, setResult] = useState<ScanResult | null>(null);
  const lastTokenRef = useRef<string | null>(null);

  const verify = useScanVerify();

  const submitToken = useCallback(
    (token: string) => {
      if (verify.isPending) return;
      if (lastTokenRef.current === token) return;
      lastTokenRef.current = token;
      verify.mutate(
        { token },
        {
          onSuccess: ({ data }) => setResult(data),
          onError: (err) => {
            const msg =
              err instanceof ApiError
                ? err.message
                : t('scan.error.generic', 'Could not verify the scan.');
            toast.error(msg);
            lastTokenRef.current = null;
          },
        }
      );
    },
    [verify, t]
  );

  const submitManual = useCallback(() => {
    const id = manualId.trim();
    if (!id || verify.isPending) return;
    verify.mutate(
      { employee_id: id },
      {
        onSuccess: ({ data }) => setResult(data),
        onError: (err) => {
          const msg =
            err instanceof ApiError
              ? err.message
              : t('scan.error.generic', 'Could not verify the scan.');
          toast.error(msg);
        },
      }
    );
  }, [manualId, verify, t]);

  const dismissResult = useCallback(() => {
    setResult(null);
    setManualId('');
    lastTokenRef.current = null;
  }, []);

  return (
    <div className="space-y-4 max-w-3xl mx-auto">
      <div>
        <h2 className="text-lg font-medium">{t('nav.scans', 'Scans')}</h2>
        <p className="text-sm text-muted-foreground">
          {t('scan.subtitle', 'Verify a worker or piece of equipment at the gate.')}
        </p>
      </div>

      {/* Mode toggle */}
      <div className="flex items-center gap-1 border border-border rounded-md p-0.5 w-fit">
        <ModeButton
          active={mode === 'qr'}
          onClick={() => setMode('qr')}
          icon={<ScanLine className="size-3.5" />}
          label={t('scan.mode_qr', 'Scan QR')}
        />
        <ModeButton
          active={mode === 'manual'}
          onClick={() => setMode('manual')}
          icon={<Keyboard className="size-3.5" />}
          label={t('scan.mode_manual', 'Manual entry')}
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>
            {mode === 'qr'
              ? t('scan.qr_title', 'Camera scanner')
              : t('scan.manual_title', 'Manual entry')}
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {mode === 'qr' ? (
            <QrScanner onDecoded={submitToken} paused={!!result || verify.isPending} />
          ) : (
            <ManualEntry
              value={manualId}
              onChange={setManualId}
              onSubmit={submitManual}
              isSubmitting={verify.isPending}
            />
          )}
          {verify.isPending && (
            <p className="text-sm text-muted-foreground">
              {t('scan.verifying', 'Verifying…')}
            </p>
          )}
        </CardContent>
      </Card>

      {result && <ScanResultScreen result={result} onDismiss={dismissResult} />}
    </div>
  );
}

function ModeButton({
  active,
  onClick,
  icon,
  label,
}: {
  active: boolean;
  onClick: () => void;
  icon: React.ReactNode;
  label: string;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={[
        'inline-flex items-center gap-1.5 h-7 px-2.5 text-[13px] rounded-sm transition-colors',
        active
          ? 'bg-foreground text-background'
          : 'text-muted-foreground hover:text-foreground',
      ].join(' ')}
    >
      {icon}
      {label}
    </button>
  );
}

function ManualEntry({
  value,
  onChange,
  onSubmit,
  isSubmitting,
}: {
  value: string;
  onChange: (v: string) => void;
  onSubmit: () => void;
  isSubmitting: boolean;
}) {
  const { t } = useTranslation();
  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        onSubmit();
      }}
      className="flex flex-col sm:flex-row gap-2"
    >
      <Input
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={t('scan.manual_placeholder', 'Employee ID (e.g. EMP-001)')}
        autoFocus
        disabled={isSubmitting}
      />
      <Button type="submit" disabled={!value.trim() || isSubmitting}>
        {t('scan.verify', 'Verify')}
      </Button>
    </form>
  );
}
