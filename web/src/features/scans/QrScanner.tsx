import { BrowserMultiFormatReader, type IScannerControls } from '@zxing/browser';
import { Camera } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';

interface QrScannerProps {
  onDecoded: (text: string) => void;
  /** Pause decoding while a result is being shown / submitted. */
  paused?: boolean;
}

/**
 * Camera viewport + continuous QR decode via @zxing/browser.
 *
 *   - getUserMedia permission requested in-context (only when this
 *     component mounts, never at app start).
 *   - Picks the rear camera when one is available (facingMode: environment).
 *   - Continuous decode loop; each decoded payload bubbles via onDecoded.
 *     Caller is responsible for de-duping rapid repeats.
 *   - Stops the camera + decoder cleanly on unmount.
 *   - Visual frame overlay shows where to centre the QR. Full-bleed
 *     viewport on small screens — works in landscape and portrait.
 */
export function QrScanner({ onDecoded, paused = false }: QrScannerProps) {
  const { t } = useTranslation();
  const videoRef = useRef<HTMLVideoElement | null>(null);
  const controlsRef = useRef<IScannerControls | null>(null);
  const [error, setError] = useState<'denied' | 'unavailable' | null>(null);

  useEffect(() => {
    if (paused) return;

    const video = videoRef.current;
    if (!video) return;

    let cancelled = false;
    const reader = new BrowserMultiFormatReader();

    (async () => {
      try {
        const devices = await BrowserMultiFormatReader.listVideoInputDevices();
        // Prefer the back camera if one is identifiable by label.
        const rearCam = devices.find((d) => /back|rear|environment/i.test(d.label));
        const constraints: MediaStreamConstraints = rearCam
          ? { video: { deviceId: { exact: rearCam.deviceId } } }
          : { video: { facingMode: { ideal: 'environment' } } };

        const controls = await reader.decodeFromConstraints(
          constraints,
          video,
          (decoded, err) => {
            if (cancelled) return;
            if (decoded) {
              onDecoded(decoded.getText());
            }
            // err is a NotFoundException on every empty frame — ignore.
            void err;
          }
        );
        controlsRef.current = controls;
      } catch (err) {
        if (cancelled) return;
        const isPermission =
          err instanceof DOMException &&
          (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError');
        setError(isPermission ? 'denied' : 'unavailable');
      }
    })();

    return () => {
      cancelled = true;
      controlsRef.current?.stop();
      controlsRef.current = null;
    };
  }, [onDecoded, paused]);

  if (error === 'denied') {
    return (
      <CameraFallback
        title={t('scan.error.permission_title', 'Camera permission denied')}
        body={t(
          'scan.error.permission_body',
          'Allow camera access in your browser to scan QR codes, or use manual entry.'
        )}
      />
    );
  }
  if (error === 'unavailable') {
    return (
      <CameraFallback
        title={t('scan.error.unavailable_title', 'Camera unavailable')}
        body={t(
          'scan.error.unavailable_body',
          'No camera is available on this device. Use manual entry instead.'
        )}
      />
    );
  }

  return (
    <div className="relative w-full aspect-video bg-black overflow-hidden rounded-md border border-border">
      <video
        ref={videoRef}
        className="absolute inset-0 h-full w-full object-cover"
        muted
        autoPlay
        playsInline
      />
      {/* Alignment frame */}
      <div className="absolute inset-0 grid place-items-center pointer-events-none">
        <div className="relative w-2/3 max-w-72 aspect-square">
          <div className="absolute -top-px -start-px size-8 border-t-2 border-s-2 border-white" />
          <div className="absolute -top-px -end-px size-8 border-t-2 border-e-2 border-white" />
          <div className="absolute -bottom-px -start-px size-8 border-b-2 border-s-2 border-white" />
          <div className="absolute -bottom-px -end-px size-8 border-b-2 border-e-2 border-white" />
        </div>
      </div>
      <div className="absolute bottom-3 inset-x-0 text-center text-white/80 text-xs">
        {t('scan.hint', 'Centre the QR code in the frame')}
      </div>
    </div>
  );
}

function CameraFallback({ title, body }: { title: string; body: string }) {
  return (
    <div className="w-full aspect-video grid place-items-center bg-muted rounded-md border border-border">
      <div className="text-center max-w-sm px-6">
        <Camera className="mx-auto size-8 text-muted-foreground" />
        <h3 className="mt-3 text-sm font-medium">{title}</h3>
        <p className="mt-1 text-xs text-muted-foreground">{body}</p>
        <Button
          variant="secondary"
          size="sm"
          className="mt-3"
          onClick={() => window.location.reload()}
        >
          Retry
        </Button>
      </div>
    </div>
  );
}
