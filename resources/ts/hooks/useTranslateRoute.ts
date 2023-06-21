import { useCallback } from "react";
import { useTranslation } from "react-i18next";

export const useTranslateRoute = () => {
  const { t } = useTranslation();

  return useCallback(
    (path: string) => {
      if (path === "/") return path;

      return t(`pages.${path}`);
    },
    [t]
  );
};
