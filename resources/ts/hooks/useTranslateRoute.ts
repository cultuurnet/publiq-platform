import { useCallback } from "react";
import { useTranslation } from "react-i18next";

export const useTranslateRoute = () => {
  const { t } = useTranslation();

  const translate = useCallback((path: string) => t(`pages.${path}`), [t]);

  return translate;
};
