import { useCallback } from "react";
import { useTranslation } from "react-i18next";

export const useTranslateRoute = () => {
  const { t } = useTranslation();

  return useCallback(
    (
      path: string,
      language?: string,
      variables: Record<string, unknown> = {}
    ) => {
      return t(`pages.${path}`, {
        lng: language,
        ...variables,
      });
    },
    [t]
  );
};
