import React, { useMemo } from "react";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { usePage } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import type { Integration } from "../types/Integration";
import type { PageProps, WidgetConfigVariables } from "../types/PageProps";

export const UitIdWidget = ({
  url,
  profileUrl,
  registerUrl,
  oAuthDomain,
}: WidgetConfigVariables) => {
  const { i18n } = useTranslation();
  const translateRoute = useTranslateRoute();
  const { component } = usePage();
  const { props } = usePage<
    PageProps & {
      integration?: Integration;
    }
  >();

  const widgetConfig = useMemo(
    () =>
      JSON.stringify({
        $schema: `${url}config-schema.json`,
        applicationName: "publiq platform",
        uitidProfileUrl: profileUrl,
        uitidRegisterUrl: registerUrl,
        defaultLanguage: "nl",
        auth0Domain: oAuthDomain,
        loginUrl: "/login",
        logoutUrl: "/logout",
        accessTokenCookieName: "",
        idTokenCookieName: "auth.token.idToken",
        actions: [
          {
            url: {
              nl: "/nl/integraties",
              en: "/en/integrations",
            },
            label: {
              nl: "Mijn integraties",
              en: "My integrations",
            },
          },
        ],
      }),
    [oAuthDomain, profileUrl, registerUrl, url]
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

  const currentPageToVariables: { [key: string]: { [key: string]: unknown } } =
    {
      "integrations/detail": {
        id: props.integration?.id,
      },
    };

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
              href={`${translateRoute(`/${currentPage}`, "nl", currentPageToVariables[currentPage])}?setLocale=true`}
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
              href={`${translateRoute(`/${currentPage}`, "en", currentPageToVariables[currentPage])}?setLocale=true`}
            >
              EN
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};
