import React, { ComponentProps, useMemo, useState } from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";
import { ButtonSecondary, ButtonSecondaryProps } from "./ButtonSecondary";
import { usePage, router } from "@inertiajs/react";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { PubliqLogo } from "./logos/PubliqLogo";

type LanguageButtonProps = ButtonSecondaryProps;

const LanguageButton = ({
  orientation,
  children,
  ...props
}: LanguageButtonProps) => {
  if (orientation === "vertical") {
    return <ButtonSecondary {...props}>{children}</ButtonSecondary>;
  }

  return <button {...props}>{children}</button>;
};

type Props = ComponentProps<"section"> & {
  orientation?: "vertical" | "horizontal";
};

export default function Navigation({
  className,
  children,
  orientation = "horizontal",
  ...props
}: Props) {
  const { t, i18n } = useTranslation();
  const translateRoute = useTranslateRoute();

  const path = new URL(document.location.href).pathname;

  const pages = ["integrations", "integrations/new", "support"];

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

  const classes = classNames(
    "flex md:items-center md:justify-around max-md:p-4 max-md:gap-5",
    orientation === "vertical" && "flex-col",
    className
  );

  const [activeTab, setActiveTab] = useState("");

  return (
    <section className={classes} {...props}>
      {children && <div className="fixed top-10 right-16">{children}</div>}
      <Link href="/">
        <PubliqLogo color="publiq-blue" width={50} height={50} />
      </Link>
      <div className="flex max-md:flex-col md:gap-8 min-w-[50%]">
        {pages.map((pageTitle) => (
          <Link
            key={pageTitle}
            href={translateRoute(`/${pageTitle}`)}
            onClick={() => setActiveTab(pageTitle)}
            className={classNames(
              "max-md:inline-flex items-center justify-between py-3 border-transparent border-b-4",
              activeTab === pageTitle &&
                "md:border-b-4 md:border-b-publiq-blue max-md:font-semibold"
            )}
          >
            <Heading level={5} className="max-md:text-xl">
              {t(`nav.${pageTitle}`)}
            </Heading>
            {orientation === "vertical" && (
              <FontAwesomeIcon icon={faChevronRight} />
            )}
          </Link>
        ))}
      </div>
      <div className="flex max-md:flex-col-reverse md:items-center gap-3 border-transparent border-b-4">
        <div className="flex gap-2 max-md:self-center max-md:fixed max-md:bottom-[5rem]">
          <LanguageButton
            orientation={orientation}
            className={classNames(
              "max-md:py-1 md:hover:underline",
              i18n.language === "nl" &&
                "max-md:bg-publiq-blue-dark max-md:bg-opacity-10 md:font-semibold"
            )}
            onClick={() => {
              if (i18n.language === "nl") return;

              i18n.changeLanguage("nl");
              router.replace(`${translateRoute(`/${currentPage}`)}`);
            }}
          >
            NL
          </LanguageButton>
          <LanguageButton
            orientation={orientation}
            className={classNames(
              "max-md:py-1 md:hover:underline",
              i18n.language === "en" &&
                "max-md:bg-publiq-blue-dark max-md:bg-opacity-10 md:font-semibold"
            )}
            onClick={() => {
              if (i18n.language === "en") return;

              i18n.changeLanguage("en");
              router.replace(`${translateRoute(`/${currentPage}`)}`, {});
            }}
          >
            EN
          </LanguageButton>
        </div>

        <div className="max-md:flex max-md:align-start max-md:text-xl max-md:border-t max-md:pt-10 md:border-l md:pl-3 gap-1">
          <span className="max-md:order-last">🧙‍♂️</span>
          <span>{t("nav.hello")}, Corneel</span>
        </div>
      </div>
    </section>
  );
}
