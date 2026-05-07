import { LogOut } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';

import { authStorage } from '@/api/auth-storage';
import { endpoints } from '@/api/client';
import { ApiError } from '@/api/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/hooks/useAuth';

export function SettingsPage() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const { user, clear } = useAuth();
  const [signingOut, setSigningOut] = useState(false);

  if (!user) {
    return <div className="text-sm text-muted-foreground">{t('common.loading', 'Loading…')}</div>;
  }

  function setLanguage(lang: 'en' | 'ar') {
    void i18n.changeLanguage(lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
  }

  async function onSignOut() {
    setSigningOut(true);
    try {
      await endpoints.logout();
    } catch (err) {
      // 401 means the token was already invalid — fine, fall through.
      if (!(err instanceof ApiError) || err.status !== 401) {
        toast.error(
          err instanceof ApiError
            ? err.message
            : t('settings.signout_failed', 'Could not sign out cleanly.')
        );
      }
    } finally {
      authStorage.clear();
      clear();
      navigate('/login', { replace: true });
    }
  }

  const currentLang = i18n.language.startsWith('ar') ? 'ar' : 'en';

  return (
    <div className="space-y-4 max-w-3xl">
      <div>
        <h2 className="text-lg font-medium">{t('nav.settings', 'Settings')}</h2>
        <p className="text-sm text-muted-foreground">
          {t('settings.subtitle', 'Your profile, language preference, and active session.')}
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{t('settings.profile', 'Profile')}</CardTitle>
          <CardDescription>
            {t(
              'settings.profile_desc',
              'Read-only for v1.0 — updates land via the platform admin.'
            )}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <dl className="grid grid-cols-[160px_1fr] gap-y-2 text-sm">
            <Field label={t('settings.name', 'Name')} value={user.name} />
            <Field label={t('settings.email', 'Email')} value={user.email} mono />
            <Field label={t('settings.phone', 'Phone')} value={user.phone ?? '—'} mono />
            <Field
              label={t('settings.locale', 'Account locale')}
              value={user.locale ?? '—'}
              mono
            />
          </dl>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{t('settings.organizations', 'Organisations')}</CardTitle>
          <CardDescription>
            {t(
              'settings.organizations_desc',
              'Memberships granted to this account. The default determines which dashboard you see.'
            )}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {user.organizations.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              {t('settings.no_orgs', 'You are not attached to any organisation.')}
            </p>
          ) : (
            <ul className="divide-y divide-border -mx-4">
              {user.organizations.map((org) => (
                <li
                  key={org.id}
                  className="flex items-center justify-between gap-3 px-4 py-2.5"
                >
                  <div className="min-w-0">
                    <p className="text-sm truncate">
                      {currentLang === 'ar' ? org.name_ar : org.name_en}
                    </p>
                    <p className="text-xs text-muted-foreground mono">{org.role}</p>
                  </div>
                  {org.is_default && (
                    <Badge variant="outline" className="shrink-0">
                      {t('settings.default_org', 'Default')}
                    </Badge>
                  )}
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{t('settings.language', 'Language')}</CardTitle>
          <CardDescription>
            {t(
              'settings.language_desc',
              'Switches the UI between English and Arabic; layout flips to RTL automatically for Arabic.'
            )}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="inline-flex items-center gap-1 border border-border rounded-md p-0.5">
            <LangButton
              active={currentLang === 'en'}
              onClick={() => setLanguage('en')}
              label="English"
            />
            <LangButton
              active={currentLang === 'ar'}
              onClick={() => setLanguage('ar')}
              label="العربية"
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{t('settings.session', 'Session')}</CardTitle>
          <CardDescription>
            {t(
              'settings.session_desc',
              'Sign out to revoke this device’s access token on the server.'
            )}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Button
            variant="destructive"
            size="sm"
            onClick={onSignOut}
            disabled={signingOut}
          >
            <LogOut className="size-4 me-2 rtl:rotate-180" />
            {signingOut ? t('settings.signing_out', 'Signing out…') : t('settings.sign_out', 'Sign out')}
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}

function Field({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <>
      <dt className="text-xs text-muted-foreground">{label}</dt>
      <dd className={mono ? 'mono tabular-nums' : ''}>{value}</dd>
    </>
  );
}

function LangButton({
  active,
  onClick,
  label,
}: {
  active: boolean;
  onClick: () => void;
  label: string;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={[
        'inline-flex items-center h-7 px-3 text-[13px] rounded-sm transition-colors',
        active
          ? 'bg-foreground text-background'
          : 'text-muted-foreground hover:text-foreground',
      ].join(' ')}
    >
      {label}
    </button>
  );
}
