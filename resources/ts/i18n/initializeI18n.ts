import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import nl from "../../translations/nl.json";
import en from "../../translations/en.json";
import LanguageDetector from "i18next-browser-languagedetector";

export const initializeI18n = async () => {
  try {
    await i18n
      .use(initReactI18next)
      .use(LanguageDetector)
      .init({
        resources: {
          nl: { translation: nl },
          en: { translation: en },
        },
        lng: "nl",
        fallbackLng: "nl",

        interpolation: {
          escapeValue: false,
        },
        detection: {
          lookupCookie: "locale",
          order: ["path", "cookie"],
          convertDetectedLanguage: (lng: string) =>
            ["en", "nl"].includes(lng) ? lng : "",
        },
      });

    i18n.changeLanguage();
  } catch (error) {
    console.error("Failed to initialize i18n:", error);
  }
};
