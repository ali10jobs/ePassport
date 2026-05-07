import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { cn } from '@/lib/cn';

/**
 * Badge — small status pill with 2-4px radius. Variants drive only the
 * surface tint; text colors stay near-black for readability against
 * light backgrounds.
 */
const badgeVariants = cva(
  'inline-flex items-center gap-1 rounded-sm px-1.5 py-0.5 text-[11px] font-medium leading-4',
  {
    variants: {
      variant: {
        neutral: 'bg-muted text-foreground/80 border border-border',
        success: 'bg-success/10 text-success border border-success/30',
        destructive:
          'bg-destructive/10 text-destructive border border-destructive/30',
        warning: 'bg-warning/15 text-warning-foreground border border-warning/40',
        primary: 'bg-primary/10 text-primary border border-primary/30',
        outline: 'border border-border text-foreground/80',
      },
    },
    defaultVariants: {
      variant: 'neutral',
    },
  }
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {}

export function Badge({ className, variant, ...props }: BadgeProps) {
  return <span className={cn(badgeVariants({ variant }), className)} {...props} />;
}
