import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";

export default function Header() {
  const { t, i18n } = useTranslation();

  return (
    <header className="mt-2 mb-4 bg-[theme(colors.blue)]">
      <p>HELLO</p>
      <Heading level={1}>{t("title")}</Heading>
      <button onClick={()=> i18n.changeLanguage('nl')}>NL</button>
      <button onClick={()=> i18n.changeLanguage('en')}>EN</button>
    </header>
  );
}
