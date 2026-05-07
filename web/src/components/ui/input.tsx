import * as React from 'react';

import { cn } from '@/lib/cn';

/**
 * Input — 1px border, 4px radius, 36px height, focus ring without glow.
 */
export const Input = React.forwardRef<
  HTMLInputElement,
  React.InputHTMLAttributes<HTMLInputElement>
>(({ className, type, ...props }, ref) => {
  return (
    <input
      ref={ref}
      type={type}
      className={cn(
        'flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-none ' +
          'transition-colors placeholder:text-muted-foreground ' +
          'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring ' +
          'disabled:cursor-not-allowed disabled:opacity-50 ' +
          'file:border-0 file:bg-transparent file:text-sm file:font-medium',
        className
      )}
      {...props}
    />
  );
});
Input.displayName = 'Input';
