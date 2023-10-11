import React, { ComponentProps, useState } from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { PubliqLogo } from "./logos/PubliqLogo";

type Props = ComponentProps<"section"> & {
  orientation?: "vertical" | "horizontal";
};

export default function Navigation({
  className,
  children,
  orientation = "horizontal",
  ...props
}: Props) {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();

  const pages = ["integrations", "integrations/new", "support"];

  const classes = classNames(
    "flex md:items-center md:justify-start gap-36 px-7 max-md:p-4 max-md:gap-5",
    orientation === "vertical" && "flex-col",
    className
  );

  const [activeLink, setActiveLink] = useState("");

  const afterStyles =
    "after:hidden md:after:hover:block md:after:hover:absolute md:after:hover:top-[3rem] md:after:hover:left-1/4 md:after:hover:w-14 md:after:hover:h-0 md:after:hover:border-b-4 md:after:hover:border-b-publiq-blue";

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
            onClick={() => setActiveLink(pageTitle)}
            className={classNames(
              "relative max-md:inline-flex items-center justify-between py-3 border-transparent border-b-4 ",
              activeLink === pageTitle &&
                "md:border-b-4 md:border-b-publiq-blue max-md:font-semibold",
              afterStyles
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
    </section>
  );
}
