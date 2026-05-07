import { ArrowLeft, ImageOff, Lock, MapPin, MessageSquare } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';
import { toast } from 'sonner';

import { ApiError, type HazardNote } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { HazardStatusBadge, SeverityBadge } from '@/components/shared/StatusBadge';
import { useAddHazardNote, useHazard, useUpdateHazardStatus } from '@/hooks/useHazards';

const STATUS_OPTIONS = [
  'submitted',
  'under_review',
  'action_issued',
  'resolved',
  'dismissed',
] as const;

export function HazardDetailPage() {
  const { t } = useTranslation();
  const { id } = useParams<{ id: string }>();
  const { data, isLoading, isError, error } = useHazard(id);
  const updateStatus = useUpdateHazardStatus(id ?? '');
  const addNote = useAddHazardNote(id ?? '');

  const [resolutionSummary, setResolutionSummary] = useState('');
  const [noteType, setNoteType] = useState<'internal' | 'public'>('public');
  const [noteBody, setNoteBody] = useState('');

  if (isLoading) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }
  if (isError || !data) {
    return (
      <div className="text-sm text-destructive">
        {(error as Error)?.message ?? t('hazards.detail.error', 'Could not load hazard.')}
      </div>
    );
  }

  const hazard = data.data;

  function onStatusChange(next: string) {
    if (next === hazard.status) return;
    updateStatus.mutate(
      next === 'resolved' && resolutionSummary
        ? { status: next, resolution_summary: resolutionSummary }
        : { status: next },
      {
        onSuccess: () => toast.success(t('hazards.toast.status_updated', 'Status updated.')),
        onError: (err) =>
          toast.error(
            err instanceof ApiError ? err.message : t('hazards.toast.error', 'Action failed.')
          ),
      }
    );
  }

  function onAddNote(e: React.FormEvent) {
    e.preventDefault();
    const body = noteBody.trim();
    if (!body) return;
    addNote.mutate(
      { note_type: noteType, body, body_lang: 'en' },
      {
        onSuccess: () => {
          toast.success(
            noteType === 'public'
              ? t('hazards.toast.public_note', 'Public note added.')
              : t('hazards.toast.internal_note', 'Internal note added.')
          );
          setNoteBody('');
        },
        onError: (err) =>
          toast.error(
            err instanceof ApiError ? err.message : t('hazards.toast.error', 'Action failed.')
          ),
      }
    );
  }

  const internalNotes = hazard.notes.filter((n) => n.note_type === 'internal');
  const publicNotes = hazard.notes.filter((n) => n.note_type === 'public');

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-start gap-3">
        <Link to="/hazards">
          <Button variant="ghost" size="icon" aria-label={t('common.back', 'Back')}>
            <ArrowLeft className="size-4 rtl:rotate-180" />
          </Button>
        </Link>
        <div className="flex-1 min-w-0">
          <div className="flex items-baseline gap-2 flex-wrap">
            <h2 className="text-lg font-medium mono">{hazard.anonymous_report_id.slice(0, 8)}…</h2>
            <SeverityBadge severity={hazard.severity} />
            <HazardStatusBadge status={hazard.status} />
            <span className="text-sm text-muted-foreground">
              · {t(`hazards.category.${hazard.category}`, hazard.category)}
            </span>
          </div>
          <p className="text-xs text-muted-foreground mono mt-0.5">
            {hazard.anonymous_report_id}
          </p>
        </div>

        <div className="flex items-center gap-2 shrink-0">
          <Select
            value={hazard.status}
            onChange={(e) => onStatusChange(e.target.value)}
            disabled={updateStatus.isPending}
            className="h-9 w-44"
            aria-label={t('hazards.detail.status', 'Status')}
          >
            {STATUS_OPTIONS.map((s) => (
              <option key={s} value={s}>
                {t(`status.hazard.${s}`, s.replace(/_/g, ' '))}
              </option>
            ))}
          </Select>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {/* Description + photo */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>{t('hazards.detail.description', 'Description')}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm whitespace-pre-wrap">
              {hazard.description ?? (
                <span className="text-muted-foreground">
                  {t('hazards.detail.no_description', 'No description provided.')}
                </span>
              )}
            </p>

            {/* Photo placeholder — backend stores a path; signed-URL serving lands later. */}
            <div className="aspect-video bg-muted rounded-md border border-border grid place-items-center">
              <div className="text-center text-muted-foreground">
                <ImageOff className="mx-auto size-6" />
                <p className="text-xs mt-2">
                  {t(
                    'hazards.detail.photo_unavailable',
                    'Photo stored — signed-URL display lands in a later phase.'
                  )}
                </p>
                {hazard.photo_path && (
                  <p className="text-[11px] mono mt-1 truncate max-w-xs">{hazard.photo_path}</p>
                )}
              </div>
            </div>

            {/* GPS */}
            {hazard.latitude !== null && hazard.longitude !== null && (
              <div className="flex items-center gap-2 text-sm">
                <MapPin className="size-4 text-muted-foreground" />
                <a
                  className="mono tabular-nums hover:underline"
                  href={`https://www.openstreetmap.org/?mlat=${hazard.latitude}&mlon=${hazard.longitude}#map=18/${hazard.latitude}/${hazard.longitude}`}
                  target="_blank"
                  rel="noreferrer"
                >
                  {hazard.latitude.toFixed(5)}, {hazard.longitude.toFixed(5)}
                </a>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Resolution summary input (only meaningful pre-resolved) */}
        <Card>
          <CardHeader>
            <CardTitle>{t('hazards.detail.resolution', 'Resolution')}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            {hazard.resolution_summary ? (
              <p className="whitespace-pre-wrap">{hazard.resolution_summary}</p>
            ) : (
              <>
                <p className="text-muted-foreground">
                  {t(
                    'hazards.detail.resolution_hint',
                    'Optional resolution summary that surfaces on the public status page when this hazard is marked resolved.'
                  )}
                </p>
                <Input
                  value={resolutionSummary}
                  onChange={(e) => setResolutionSummary(e.target.value)}
                  placeholder={t(
                    'hazards.detail.resolution_placeholder',
                    'Guardrails installed and inspected by HSE.'
                  )}
                />
                <Button
                  variant="secondary"
                  size="sm"
                  disabled={!resolutionSummary.trim() || hazard.status === 'resolved'}
                  onClick={() =>
                    updateStatus.mutate(
                      { status: 'resolved', resolution_summary: resolutionSummary.trim() },
                      {
                        onSuccess: () => {
                          toast.success(t('hazards.toast.resolved', 'Hazard resolved.'));
                          setResolutionSummary('');
                        },
                      }
                    )
                  }
                >
                  {t('hazards.detail.mark_resolved', 'Resolve with summary')}
                </Button>
              </>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Notes — internal vs public */}
      <Card>
        <CardHeader>
          <CardTitle>{t('hazards.detail.notes', 'Notes')}</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {/* Add-note form */}
          <form
            onSubmit={onAddNote}
            className="space-y-2 border border-border rounded-md p-3 bg-muted/30"
          >
            <div className="flex items-center gap-2">
              <NoteTypeChip
                active={noteType === 'public'}
                onClick={() => setNoteType('public')}
                label={t('hazards.detail.note_public', 'Public')}
                icon={<MessageSquare className="size-3.5" />}
              />
              <NoteTypeChip
                active={noteType === 'internal'}
                onClick={() => setNoteType('internal')}
                label={t('hazards.detail.note_internal', 'Internal')}
                icon={<Lock className="size-3.5" />}
              />
              <span className="text-xs text-muted-foreground ms-auto">
                {noteType === 'public'
                  ? t('hazards.detail.note_public_hint', 'Visible to the anonymous submitter.')
                  : t('hazards.detail.note_internal_hint', 'NOT visible to the submitter.')}
              </span>
            </div>
            <Input
              value={noteBody}
              onChange={(e) => setNoteBody(e.target.value)}
              placeholder={
                noteType === 'public'
                  ? t(
                      'hazards.detail.note_public_placeholder',
                      'e.g. Safety team is on site now and barriers are being installed.'
                    )
                  : t(
                      'hazards.detail.note_internal_placeholder',
                      'e.g. Reviewed CCTV. Suspect contractor crew B.'
                    )
              }
              disabled={addNote.isPending}
            />
            <div className="flex justify-end">
              <Button type="submit" size="sm" disabled={!noteBody.trim() || addNote.isPending}>
                {addNote.isPending
                  ? t('common.loading', 'Loading…')
                  : t('hazards.detail.add_note', 'Add note')}
              </Button>
            </div>
          </form>

          {/* Existing notes */}
          {hazard.notes.length === 0 ? (
            <p className="text-sm text-muted-foreground text-center py-4">
              {t('hazards.detail.notes_empty', 'No notes yet.')}
            </p>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <NoteColumn
                title={t('hazards.detail.notes_public_title', 'Public')}
                hint={t(
                  'hazards.detail.notes_public_subtitle',
                  'Shown on the public status page.'
                )}
                tone="public"
                notes={publicNotes}
              />
              <NoteColumn
                title={t('hazards.detail.notes_internal_title', 'Internal')}
                hint={t(
                  'hazards.detail.notes_internal_subtitle',
                  'Visible only inside this organisation.'
                )}
                tone="internal"
                notes={internalNotes}
              />
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

function NoteTypeChip({
  active,
  onClick,
  label,
  icon,
}: {
  active: boolean;
  onClick: () => void;
  label: string;
  icon: React.ReactNode;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={[
        'inline-flex items-center gap-1.5 h-7 px-2 text-[12px] rounded-sm transition-colors',
        active
          ? 'bg-foreground text-background'
          : 'text-muted-foreground hover:text-foreground border border-border',
      ].join(' ')}
    >
      {icon}
      {label}
    </button>
  );
}

function NoteColumn({
  title,
  hint,
  tone,
  notes,
}: {
  title: string;
  hint: string;
  tone: 'public' | 'internal';
  notes: HazardNote[];
}) {
  return (
    <div className="space-y-2">
      <div>
        <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
          {title}
        </p>
        <p className="text-[11px] text-muted-foreground/80">{hint}</p>
      </div>
      {notes.length === 0 ? (
        <p className="text-xs text-muted-foreground py-2">—</p>
      ) : (
        <ul className="space-y-1.5">
          {notes.map((n) => (
            <li
              key={n.id}
              className={[
                'rounded-md border px-3 py-2',
                tone === 'public'
                  ? 'border-success/30 bg-success/5'
                  : 'border-border bg-muted/40',
              ].join(' ')}
            >
              <p className="text-sm whitespace-pre-wrap">{n.body}</p>
              <p className="text-[11px] mono tabular-nums text-muted-foreground mt-1">
                {n.created_at.replace('T', ' ').slice(0, 16)}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
