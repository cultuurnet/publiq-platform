import React, { ComponentProps, useMemo } from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";
import { SecondaryButton, SecondaryButtonProps } from "./SecondaryButton";
import { usePage } from "@inertiajs/react";
import { router } from "@inertiajs/core";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

type LanguageButtonProps = SecondaryButtonProps;

const LanguageButton = ({
  orientation,
  children,
  ...props
}: LanguageButtonProps) => {
  if (orientation === "vertical") {
    return <SecondaryButton {...props}>{children}</SecondaryButton>;
  }

  return <button {...props}>{children}</button>;
};

type Props = ComponentProps<"section"> & {
  orientation?: "vertical" | "horizontal";
  visible?: boolean;
  isVisible?: boolean;
  setIsVisible?: React.Dispatch<React.SetStateAction<boolean>>;
};

export default function Navigation({
  className,
  children,
  visible = true,
  isVisible,
  orientation = "horizontal",
  setIsVisible,
  ...props
}: Props) {
  const { t, i18n } = useTranslation();

  const path = new URL(document.location.href).pathname;

  const pages = ["integrations", "support"];

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
    "flex md:items-center md:justify-around max-md:p-12 max-md:gap-5",
    !visible && "hidden",
    orientation === "vertical" && "flex-col",
    className
  );

  return (
    <section className={classes} {...props}>
      {children && <div className="fixed top-10 right-16">{children}</div>}
      <Link href="/">
        <Heading
          className="max-md:text-2xl py-2 border-transparent border-b-4"
          level={3}
        >
          {t("title")}
        </Heading>
      </Link>
      <div className="flex max-md:flex-col md:gap-8 min-w-[50%]">
        {pages.map((pageTitle) => (
          <Link
            key={pageTitle}
            href={t(`pages./${pageTitle}`)}
            className={classNames(
              "max-md:inline-flex items-center justify-between py-3 border-transparent border-b-4",
              path.startsWith(`${t(`pages./${pageTitle}`)}`) &&
                "md:border-b-4 md:border-b-publiq-blue max-md:font-semibold"
            )}
            onClick={() => setIsVisible && setIsVisible((prev) => !prev)}
          >
            <Heading level={5} className="max-md:text-xl">
              {t(`nav.${pageTitle}`)}
            </Heading>
            {isVisible && <FontAwesomeIcon icon={faChevronRight} />}
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
              i18n.changeLanguage("nl");
              router.replace(`${t(`pages./${currentPage}`)}`);
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
              i18n.changeLanguage("en");
              router.replace(`${t(`pages./${currentPage}`)}`, {});
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
