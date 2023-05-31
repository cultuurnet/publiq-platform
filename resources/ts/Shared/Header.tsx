import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

export default function Header() {
  const { t, i18n } = useTranslation();

  return (
    <header className="flex items-center justify-around w-full mb-4 py-4 bg-white">
      <a>
        <Heading className="text-base" level={1}>
          {t("title")}
        </Heading>
      </a>
      <div className="flex gap-8 min-w-[50%]">
        <Link
          href={"/integrations"}
          className={classNames(
            "py-1",
            "/integrations".startsWith("/integrations") &&
              "border-b-2 border-b-blue"
          )}
        >
          {t("nav.integrations")}
        </Link>
        <Link href="#">{t("nav.opportunities")}</Link>
        <Link href="#">{t("nav.support")}</Link>
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
