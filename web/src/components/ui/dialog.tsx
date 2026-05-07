import { X } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/cn';

interface DialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  children: React.ReactNode;
  /** When true, clicking the backdrop or pressing Esc does NOT close. */
  static?: boolean;
}

/**
 * Minimal headless dialog — no Radix dep. Backdrop + centred panel.
 * Closes on Esc + backdrop click unless `static` is set.
 */
export function Dialog({ open, onOpenChange, children, static: isStatic }: DialogProps) {
  React.useEffect(() => {
    if (!open) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === 'Escape' && !isStatic) onOpenChange(false);
    }
    window.addEventListener('keydown', onKey);
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      window.removeEventListener('keydown', onKey);
      document.body.style.overflow = prev;
    };
  }, [open, onOpenChange, isStatic]);

  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
    >
      <div
        className="absolute inset-0 bg-black/50"
        onClick={() => !isStatic && onOpenChange(false)}
      />
      {children}
    </div>
  );
}

/**
 * Standard dialog panel — 1px border, 6px radius, white surface.
 */
export function DialogPanel({
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'relative z-10 w-full max-w-lg max-h-[85vh] flex flex-col',
        'rounded-lg border border-border bg-card text-card-foreground shadow-md',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

export function DialogHeader({
  className,
  onClose,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & { onClose?: () => void }) {
  return (
    <div
      className={cn(
        'flex items-start justify-between gap-3 px-5 pt-5 pb-3 border-b border-border',
        className
      )}
      {...props}
    >
      <div className="flex-1 min-w-0">{children}</div>
      {onClose && (
        <Button
          variant="ghost"
          size="icon"
          className="size-7 shrink-0 -mt-1 -me-1"
          onClick={onClose}
          aria-label="Close"
        >
          <X className="size-4" />
        </Button>
      )}
    </div>
  );
}

export function DialogTitle({
  className,
  ...props
}: React.HTMLAttributes<HTMLHeadingElement>) {
  return <h2 className={cn('text-base font-medium leading-tight', className)} {...props} />;
}

export function DialogDescription({
  className,
  ...props
}: React.HTMLAttributes<HTMLParagraphElement>) {
  return (
    <p
      className={cn('text-sm text-muted-foreground mt-1 leading-snug', className)}
      {...props}
    />
  );
}

export function DialogContent({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('px-5 py-4 overflow-y-auto flex-1', className)} {...props} />;
}

export function DialogFooter({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'flex items-center justify-end gap-2 border-t border-border px-5 py-3',
        className
      )}
      {...props}
    />
  );
}
