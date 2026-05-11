import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { z } from 'zod';

import { authStorage } from '@/api/auth-storage';
import { endpoints } from '@/api/client';
import { ApiError } from '@/api/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useAuth } from '@/hooks/useAuth';

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});
type LoginInput = z.infer<typeof schema>;

export function LoginPage() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const { setUser } = useAuth();
  const [submitError, setSubmitError] = useState<string | null>(null);

  const form = useForm<LoginInput>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '' },
  });

  function toggleLang() {
    const next = i18n.language.startsWith('ar') ? 'en' : 'ar';
    void i18n.changeLanguage(next);
  }

  async function onSubmit(values: LoginInput) {
    setSubmitError(null);
    try {
      const result = await endpoints.login({ ...values, mode: 'token' });
      if (result.data.access_token) {
        authStorage.set(result.data.access_token);
      }
      setUser(result.data.user);
      navigate('/dashboard', { replace: true });
    } catch (err) {
      if (err instanceof ApiError && err.code === 'UNAUTHENTICATED') {
        setSubmitError(t('auth.error_invalid_credentials'));
      } else {
        setSubmitError(t('auth.error_generic'));
      }
    }
  }

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <CardTitle className="text-base">{t('auth.login_title')}</CardTitle>
            <CardDescription className="mt-1">{t('auth.login_subtitle')}</CardDescription>
          </div>
          <button
            type="button"
            onClick={toggleLang}
            className="text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            {i18n.language.startsWith('ar') ? 'EN' : 'AR'}
          </button>
        </div>
      </CardHeader>
      <CardContent>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-3">
          <div className="flex flex-col space-y-1.5">
            <label htmlFor="email" className="text-xs font-medium text-foreground">
              {t('auth.email_label')}
            </label>
            <Input
              id="email"
              type="email"
              autoComplete="email"
              autoFocus
              disabled={form.formState.isSubmitting}
              {...form.register('email')}
            />
            {form.formState.errors.email && (
              <p className="text-xs text-destructive">{form.formState.errors.email.message}</p>
            )}
          </div>

          <div className="flex flex-col space-y-1.5">
            <label htmlFor="password" className="text-xs font-medium text-foreground">
              {t('auth.password_label')}
            </label>
            <Input
              id="password"
              type="password"
              autoComplete="current-password"
              disabled={form.formState.isSubmitting}
              {...form.register('password')}
            />
            {form.formState.errors.password && (
              <p className="text-xs text-destructive">{form.formState.errors.password.message}</p>
            )}
          </div>

          {submitError && (
            <div className="border border-destructive/40 bg-destructive/5 text-destructive text-xs px-3 py-2 rounded-md">
              {submitError}
            </div>
          )}

          <Button
            type="submit"
            className="w-full"
            disabled={form.formState.isSubmitting}
          >
            {form.formState.isSubmitting ? t('auth.submitting') : t('auth.submit')}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
