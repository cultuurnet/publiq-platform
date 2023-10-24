import React, { useMemo } from "react";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { usePage } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

export type WidgetConfigVariables = {
  profileUrl: string;
  registerUrl: string;
  auth0Domain: string;
};

export const UitIdWidget = ({
  profileUrl,
  registerUrl,
  auth0Domain,
}: WidgetConfigVariables) => {
  const { i18n } = useTranslation();
  const translateRoute = useTranslateRoute();
  const { component } = usePage();

  const widgetConfig = useMemo(
    () =>
      JSON.stringify({
        $schema: "https://assets.uit.be/uitid-widget/config-schema.json",
        applicationName: "Publiq platform",
        uitidProfileUrl: profileUrl,
        uitidRegisterUrl: registerUrl,
        defaultLanguage: "nl",
        auth0Domain: auth0Domain,
        loginUrl: "/login",
        logoutUrl: "/logout",
        accessTokenCookieName: "",
        idTokenCookieName: "auth.token.idToken",
      }),
    [auth0Domain, profileUrl, registerUrl]
  );

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
        {widgetConfig}
      </script>

      <div
        id="uitid-widget"
        data-current-page={currentPage}
        className="min-h-[40px]"
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
