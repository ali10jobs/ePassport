import { zodResolver } from '@hookform/resolvers/zod';
import { ArrowLeft } from 'lucide-react';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { z } from 'zod';

import { ApiError } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { useAuth } from '@/hooks/useAuth';
import { usePermitTypes, useProjects } from '@/hooks/useCatalogs';
import { useCreatePermit } from '@/hooks/usePermits';

const schema = z.object({
  project_id: z.string().uuid(),
  issuing_organization_id: z.string().uuid(),
  permit_type_id: z.string().uuid(),
  scope_en: z.string().min(5),
  scope_ar: z.string().optional().or(z.literal('')),
  location_description_en: z.string().optional().or(z.literal('')),
});
type FormInput = z.infer<typeof schema>;

/**
 * Simple draft-creation form. Multi-step wizard (with worker / equipment
 * attach steps) is the post-MVP polish; this single screen + the detail
 * page's existing attach panel cover the demo flow.
 */
export function NewPermitPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { user } = useAuth();
  const projects = useProjects();
  const permitTypes = usePermitTypes();
  const create = useCreatePermit();

  const form = useForm<FormInput>({
    resolver: zodResolver(schema),
    defaultValues: {
      project_id: '',
      issuing_organization_id: user?.organizations?.[0]?.id ?? '',
      permit_type_id: '',
      scope_en: '',
      scope_ar: '',
      location_description_en: '',
    },
  });

  // Default project_id once catalog loads.
  useEffect(() => {
    if (projects.data && !form.getValues('project_id')) {
      form.setValue('project_id', projects.data.data[0]?.id ?? '');
    }
  }, [projects.data, form]);

  // Default issuing_org once user loads.
  useEffect(() => {
    if (user && !form.getValues('issuing_organization_id')) {
      form.setValue('issuing_organization_id', user.organizations?.[0]?.id ?? '');
    }
  }, [user, form]);

  // Default permit_type_id once catalog loads.
  useEffect(() => {
    if (permitTypes.data && !form.getValues('permit_type_id')) {
      form.setValue('permit_type_id', permitTypes.data.data[0]?.id ?? '');
    }
  }, [permitTypes.data, form]);

  function onSubmit(values: FormInput) {
    create.mutate(values, {
      onSuccess: ({ data }) => {
        toast.success(t('permits.created', 'Permit created.'));
        navigate(`/permits/${data.id}`, { replace: true });
      },
      onError: (err) =>
        toast.error(
          err instanceof ApiError ? err.message : t('permits.actions.error', 'Action failed.')
        ),
    });
  }

  const orgs = user?.organizations ?? [];

  return (
    <div className="space-y-4 max-w-2xl">
      <div className="flex items-center gap-3">
        <Link to="/permits">
          <Button variant="ghost" size="icon" aria-label={t('common.back', 'Back')}>
            <ArrowLeft className="size-4 rtl:rotate-180" />
          </Button>
        </Link>
        <div>
          <h2 className="text-lg font-medium">{t('permits.new', 'New permit')}</h2>
          <p className="text-sm text-muted-foreground">
            {t(
              'permits.new_subtitle',
              'Create a draft. You can attach workers and equipment, then submit for consultant review.'
            )}
          </p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{t('permits.new', 'New permit')}</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <Field label={t('permits.field_project', 'Project')}>
                <Select
                  disabled={projects.isLoading}
                  {...form.register('project_id')}
                >
                  {projects.data?.data.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name_en} ({p.code})
                    </option>
                  ))}
                </Select>
              </Field>

              <Field label={t('permits.field_issuing_org', 'Issuing organization')}>
                <Select {...form.register('issuing_organization_id')}>
                  {orgs.map((o) => (
                    <option key={o.id} value={o.id}>
                      {o.name_en}
                    </option>
                  ))}
                </Select>
              </Field>

              <Field label={t('permits.field_type', 'Permit type')} className="md:col-span-2">
                <Select
                  disabled={permitTypes.isLoading}
                  {...form.register('permit_type_id')}
                >
                  {permitTypes.data?.data.map((pt) => (
                    <option key={pt.id} value={pt.id}>
                      {pt.name_en} — {pt.code}
                    </option>
                  ))}
                </Select>
              </Field>

              <Field label={t('permits.field_scope_en', 'Scope (English)')} className="md:col-span-2">
                <Input
                  placeholder={t(
                    'permits.field_scope_en_placeholder',
                    'Welding work on Level 12 west columns'
                  )}
                  {...form.register('scope_en')}
                />
                {form.formState.errors.scope_en && (
                  <p className="text-xs text-destructive mt-1">
                    {form.formState.errors.scope_en.message}
                  </p>
                )}
              </Field>

              <Field label={t('permits.field_scope_ar', 'Scope (Arabic, optional)')} className="md:col-span-2">
                <Input dir="rtl" {...form.register('scope_ar')} />
              </Field>

              <Field label={t('permits.field_location', 'Location (optional)')} className="md:col-span-2">
                <Input
                  placeholder={t(
                    'permits.field_location_placeholder',
                    'Level 12, west column row, gridline 6-8'
                  )}
                  {...form.register('location_description_en')}
                />
              </Field>
            </div>

            <div className="flex items-center justify-end gap-2 pt-2">
              <Button
                type="button"
                variant="secondary"
                onClick={() => navigate('/permits')}
              >
                {t('common.cancel', 'Cancel')}
              </Button>
              <Button type="submit" disabled={create.isPending}>
                {create.isPending
                  ? t('common.loading', 'Loading…')
                  : t('permits.create_draft', 'Create draft')}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}

function Field({
  label,
  children,
  className,
}: {
  label: string;
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <div className={className}>
      <label className="text-xs font-medium text-foreground block mb-1.5">
        {label}
      </label>
      {children}
    </div>
  );
}
