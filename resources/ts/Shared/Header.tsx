import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link, usePage } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

export default function Header() {
  const { t, i18n } = useTranslation();
  const { url } = usePage();

  const path = new URL(url, "http://localhost").pathname;

  return (
    <header className="flex items-center justify-around w-full mb-4 py-4 bg-white">
      <Link href="/">
        <Heading
          className={classNames(
            "text-base",
            "py-1",
            path === "/" && "border-b-2 border-b-blue"
          )}
          level={1}
        >
          {t("title")}
        </Heading>
      </Link>
      <div className="flex gap-8 min-w-[50%]">
        {["integrations", "opportunities", "support"].map((pageTitle) => (
          <Link
            key={pageTitle}
            href={`/${pageTitle}`}
            className={classNames(
              "py-1",
              path.startsWith(`/${pageTitle}`) && "border-b-2 border-b-blue"
            )}
          >
            {t(`nav.${pageTitle}`)}
          </Link>
        ))}
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
