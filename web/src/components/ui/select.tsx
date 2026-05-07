import * as React from 'react';

import { cn } from '@/lib/cn';

/**
 * Native <select> styled to match the Input primitive. We intentionally
 * stay native here — full Radix-popover Select is heavier than what the
 * sprint needs for filter chips, and the chrome difference is minimal.
 */
export const Select = React.forwardRef<
  HTMLSelectElement,
  React.SelectHTMLAttributes<HTMLSelectElement>
>(({ className, children, ...props }, ref) => {
  return (
    <select
      ref={ref}
      className={cn(
        'flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring',
        'disabled:cursor-not-allowed disabled:opacity-50',
        className
      )}
      {...props}
    >
      {children}
    </select>
  );
});
Select.displayName = 'Select';
