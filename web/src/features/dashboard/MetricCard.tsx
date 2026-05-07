import { ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/cn';

interface MetricCardProps {
  title: string;
  description?: string;
  /** Primary number — large, mono, tabular. */
  value: string | number;
  /** Optional secondary label rendered next to the value (e.g. "active"). */
  unit?: string;
  /** Tone shifts the value color for criticals. */
  tone?: 'default' | 'destructive' | 'warning' | 'success';
  /** Drill-down link rendered as a tiny corner arrow. */
  href?: string;
  /** Optional extra rows shown beneath the primary number. */
  breakdown?: Array<{ label: string; value: string | number }>;
}

const toneClass: Record<NonNullable<MetricCardProps['tone']>, string> = {
  default: 'text-foreground',
  destructive: 'text-destructive',
  warning: 'text-warning-foreground',
  success: 'text-success',
};

/**
 * Vercel-style metric card: small label up top, big mono number,
 * optional breakdown rows. No decorative chrome — relies on the
 * outer Card border + the typographic hierarchy.
 */
export function MetricCard({
  title,
  description,
  value,
  unit,
  tone = 'default',
  href,
  breakdown,
}: MetricCardProps) {
  const content = (
    <Card
      className={cn(
        href &&
          'hover:border-foreground/20 transition-colors duration-100 cursor-pointer'
      )}
    >
      <CardHeader>
        <div className="flex items-start justify-between gap-2">
          <div className="min-w-0">
            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
              {title}
            </CardTitle>
            {description && (
              <p className="text-xs text-muted-foreground/80 mt-1 leading-snug">{description}</p>
            )}
          </div>
          {href && (
            <ArrowRight className="size-3.5 text-muted-foreground rtl:rotate-180 shrink-0 mt-0.5" />
          )}
        </div>
      </CardHeader>
      <CardContent className="pt-0">
        <div className="flex items-baseline gap-2">
          <span
            className={cn(
              'mono tabular-nums text-3xl font-medium leading-none',
              toneClass[tone]
            )}
          >
            {value}
          </span>
          {unit && <span className="text-xs text-muted-foreground">{unit}</span>}
        </div>
        {breakdown && breakdown.length > 0 && (
          <ul className="mt-3 space-y-1.5 text-xs">
            {breakdown.map((row, i) => (
              <li key={i} className="flex items-center justify-between">
                <span className="text-muted-foreground">{row.label}</span>
                <span className="mono tabular-nums">{row.value}</span>
              </li>
            ))}
          </ul>
        )}
      </CardContent>
    </Card>
  );

  return href ? <Link to={href}>{content}</Link> : content;
}
