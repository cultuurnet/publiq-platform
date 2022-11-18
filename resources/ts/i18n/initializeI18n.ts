import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import nl from '../../translations/nl.json';

export const initializeI18n = async () => {
  try {
    await i18n.use(initReactI18next).init({
      resources: {
        nl: { translation: nl },
      },
      lng: 'nl',
      fallbackLng: 'nl',

      interpolation: {
        escapeValue: false,
      },
    });
  } catch (error) {
    console.error('Failed to initialize i18n:', error);
  }
};
