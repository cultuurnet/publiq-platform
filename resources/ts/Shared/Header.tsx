import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

export default function Header() {
  const { t, i18n } = useTranslation();

  const path = new URL(document.location.href).pathname;

  return (
    <header className="flex items-center justify-around w-full bg-white shadow-lg mt-1 z-40">
      <Link href="/">
        <Heading
          className="text-base py-3 border-transparent border-b-4"
          level={1}
        >
          {t("title")}
        </Heading>
      </Link>
      <div className="flex gap-8 min-w-[50%]">
        {["integrations", "support"].map((pageTitle) => (
          <Link
            key={pageTitle}
            href={t(`pages./${pageTitle}`)}
            className={classNames(
              "py-3 border-transparent border-b-4",
              path.startsWith(`/${pageTitle}`) && "border-b-4 border-b-blue"
            )}
          >
            {t(`nav.${pageTitle}`)}
          </Link>
        ))}
      </div>
      <div className="flex items-center gap-3 border-transparent border-b-4">
        <div className="flex gap-2">
          <button onClick={() => i18n.changeLanguage("nl")}>NL</button>
          <button onClick={() => i18n.changeLanguage("en")}>EN</button>
        </div>

        <div className="border-l pl-3">
          <span>🧙‍♂️</span>
          <span>{t("nav.hello")}, Corneel</span>
        </div>
      </div>
    </header>
  );
}
