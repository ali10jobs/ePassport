import i18n from 'i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import { initReactI18next } from 'react-i18next';

import ar from './locales/ar.json';
import en from './locales/en.json';

void i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    fallbackLng: 'en',
    supportedLngs: ['en', 'ar'],
    resources: {
      en: { translation: en },
      ar: { translation: ar },
    },
    interpolation: { escapeValue: false },
    detection: {
      order: ['localStorage', 'navigator'],
      caches: ['localStorage'],
    },
  });

// Mirror the active language onto <html lang> + <html dir> so the
// browser handles bidi correctly.
function syncDocument() {
  const lang = i18n.language || 'en';
  document.documentElement.lang = lang;
  document.documentElement.dir = lang.startsWith('ar') ? 'rtl' : 'ltr';
}
syncDocument();
i18n.on('languageChanged', syncDocument);

export default i18n;
