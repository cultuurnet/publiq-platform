import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";

export default function Header() {
  const { t, i18n } = useTranslation();

  return (
    <header className="flex items-center justify-around w-full mb-4 bg-[theme(colors.blue)]">
      <Heading level={1}>{t("title")}</Heading>
      <div className="flex gap-8 min-w-[50%]">
        <a>{t("nav.integrations")}</a>
        <a>{t("nav.opportunities")}</a>
        <a>{t("nav.support")}</a>
      </div>
      <div className="flex items-center gap-3">
        <div className="flex gap-2">
          <button onClick={() => i18n.changeLanguage("nl")}>NL</button>
          <button onClick={() => i18n.changeLanguage("en")}>EN</button>
        </div>

        <div className="border-l pl-3">
          <span>🧙‍♂️</span>
          <span>Hallo, Corneel</span>
        </div>
      </div>
    </header>
  );
}
