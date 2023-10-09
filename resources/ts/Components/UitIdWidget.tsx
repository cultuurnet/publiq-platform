import React, { useMemo } from "react";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { usePage } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

console.log("import.meta.env", import.meta.env);

const widgetConfig = {
  $schema: "https://assets.uit.be/uitid-widget/config-schema.json",
  applicationName: "Publiq platform",
  uitidProfileUrl: import.meta.env.VITE_UITID_PROFILE_URL,
  uitidRegisterUrl: import.meta.env.VITE_UITID_REGISTER_URL,
  defaultLanguage: "nl",
  auth0Domain: import.meta.env.VITE_AUTH0_DOMAIN,
  loginUrl: "/login",
  logoutUrl: "/logout",
  accessTokenCookieName: "",
  idTokenCookieName: "auth.token.idToken",
};

export const UitIdWidget = () => {
  const { i18n } = useTranslation();
  const translateRoute = useTranslateRoute();
  const { component } = usePage();

  const currentPage = useMemo(
    () =>
      component
        .split("/")
        .filter((part) => part !== "Index")
        .join("/")
        .toLowerCase() ?? "/",
    [component]
  );

  return (
    <div className="w-full px-7 bg-uitid-widget">
      <script id="uitid-widget-config" type="application/json">
        {JSON.stringify(widgetConfig)}
      </script>

      <div
        id="uitid-widget"
        data-current-page={currentPage}
        className="min-h-[50px]"
        data-language={i18n.language}
      ></div>

      <div id="uitid-widget-slot" hidden>
        <div>
          <div className="flex gap-2">
            <a
              className={classNames(i18n.language === "nl" && "active")}
              href={`${translateRoute(`/${currentPage}`, "nl")}`}
            >
              NL
            </a>
            <div
              style={{
                border: "1px solid #e5e7eb",
              }}
            ></div>
            <a
              className={classNames(i18n.language === "en" && "active")}
              href={`${translateRoute(`/${currentPage}`, "en")}`}
            >
              EN
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};
