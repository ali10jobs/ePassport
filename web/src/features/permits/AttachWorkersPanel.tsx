import { Plus, Search, X } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { ApiError } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { useAttachPermitWorkers } from '@/hooks/usePermits';
import { useWorkers } from '@/hooks/useWorkers';

interface AttachWorkersPanelProps {
  permitId: string;
}

/**
 * Compact attach panel for the draft permit detail page.
 *
 *   Search workers (debounced) -> queue per-row with role -> POST as a
 *   batch to /permits/:id/workers. Removes from queue on success.
 *
 * Equipment attach is symmetric and lives in a separate component when
 * we wire it; for week-1 demo, workers + permit submit covers the
 * critical hard-block flow.
 */
export function AttachWorkersPanel({ permitId }: AttachWorkersPanelProps) {
  const { t } = useTranslation();
  const [searchInput, setSearchInput] = useState('');
  const search = useDebouncedValue(searchInput, 250);
  const [queue, setQueue] = useState<Array<{ id: string; label: string; role: string }>>([]);

  const workers = useWorkers({ page: 1, perPage: 8, search: search || undefined });
  const attach = useAttachPermitWorkers(permitId);

  function add(worker: { id: string; full_name_en: string; employee_id: string }) {
    if (queue.some((q) => q.id === worker.id)) return;
    setQueue((q) => [
      ...q,
      {
        id: worker.id,
        label: `${worker.employee_id} • ${worker.full_name_en}`,
        role: 'worker',
      },
    ]);
  }

  function remove(id: string) {
    setQueue((q) => q.filter((x) => x.id !== id));
  }

  function setRole(id: string, role: string) {
    setQueue((q) => q.map((x) => (x.id === id ? { ...x, role } : x)));
  }

  function submit() {
    if (queue.length === 0) return;
    attach.mutate(
      { workers: queue.map((q) => ({ id: q.id, role_on_permit: q.role })) },
      {
        onSuccess: ({ data }) => {
          toast.success(
            t('permits.attach.toast', 'Attached: {{n}}, already attached: {{a}}', {
              n: data.attached,
              a: data.already_attached,
            })
          );
          setQueue([]);
          setSearchInput('');
        },
        onError: (err) =>
          toast.error(
            err instanceof ApiError ? err.message : t('permits.actions.error', 'Action failed.')
          ),
      }
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>{t('permits.attach.title', 'Attach workers')}</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        <div className="relative">
          <Search className="absolute start-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground pointer-events-none" />
          <Input
            type="search"
            placeholder={t(
              'permits.attach.search_placeholder',
              'Find by name or employee ID…'
            )}
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            className="ps-8"
          />
        </div>

        {/* Search results */}
        {searchInput.length > 0 && (
          <div className="border border-border rounded-md divide-y divide-border max-h-56 overflow-y-auto">
            {workers.isLoading ? (
              <p className="text-xs text-muted-foreground p-3">
                {t('common.loading', 'Loading…')}
              </p>
            ) : workers.data?.data.length === 0 ? (
              <p className="text-xs text-muted-foreground p-3">
                {t('permits.attach.no_results', 'No workers match.')}
              </p>
            ) : (
              workers.data?.data.map((w) => {
                const inQueue = queue.some((q) => q.id === w.id);
                return (
                  <button
                    key={w.id}
                    type="button"
                    disabled={inQueue}
                    onClick={() => add(w)}
                    className={`flex w-full items-center justify-between gap-3 px-3 py-2 text-sm text-start transition-colors ${
                      inQueue
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:bg-muted'
                    }`}
                  >
                    <div className="min-w-0">
                      <span className="mono text-xs">{w.employee_id}</span>{' '}
                      <span className="text-foreground">{w.full_name_en}</span>{' '}
                      <span className="text-muted-foreground text-xs">
                        · {w.trade ?? '—'}
                      </span>
                    </div>
                    <Plus className="size-4 text-muted-foreground" />
                  </button>
                );
              })
            )}
          </div>
        )}

        {/* Queue */}
        {queue.length > 0 && (
          <div className="space-y-2">
            <p className="text-xs uppercase tracking-wide text-muted-foreground">
              {t('permits.attach.queue', 'To attach')}{' '}
              <span className="mono tabular-nums">({queue.length})</span>
            </p>
            <ul className="space-y-1.5">
              {queue.map((q) => (
                <li
                  key={q.id}
                  className="flex items-center gap-2 border border-border rounded-md px-2 py-1.5"
                >
                  <span className="text-sm flex-1 truncate">{q.label}</span>
                  <Select
                    value={q.role}
                    onChange={(e) => setRole(q.id, e.target.value)}
                    className="h-7 w-32 text-xs"
                  >
                    <option value="worker">worker</option>
                    <option value="supervisor">supervisor</option>
                    <option value="gas_tester">gas_tester</option>
                    <option value="fire_watch">fire_watch</option>
                  </Select>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    aria-label={t('common.remove', 'Remove')}
                    onClick={() => remove(q.id)}
                  >
                    <X className="size-3.5" />
                  </Button>
                </li>
              ))}
            </ul>
          </div>
        )}

        {queue.length > 0 && (
          <div className="flex justify-end">
            <Button onClick={submit} disabled={attach.isPending}>
              {attach.isPending
                ? t('common.loading', 'Loading…')
                : t('permits.attach.submit', 'Attach {{n}}', { n: queue.length })}
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
