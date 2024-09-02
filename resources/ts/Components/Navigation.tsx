import type { ComponentProps } from "react";
import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { Link, usePage } from "@inertiajs/react";
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

  const { component } = usePage();

  const pages = [
    { component: "Integrations/Index", title: "integrations" },
    { component: "Support/Index", title: "support" },
  ];

  const classes = classNames(
    "flex md:items-center md:justify-start gap-36 px-7 max-md:p-4 max-md:gap-5",
    orientation === "vertical" && "flex-col",
    className
  );

  const afterStyles =
    "md:after:block md:after:border-b-4 md:after:absolute md:after:top-[3rem] md:after:left-0 md:after:bottom-0 md:after:right-0 md:after:border-publiq-blue md:after:transform md:after:scale-x-0 md:after:transition md:after:duration-300 md:after:hover:scale-x-100";

  return (
    <section className={classes} {...props}>
      {children && <div className="fixed top-10 right-16">{children}</div>}
      <Link href={translateRoute("/")}>
        <PubliqLogo color="publiq-blue" width={32} height={32} />
      </Link>
      <div className="flex max-md:flex-col md:gap-8 min-w-[50%]">
        {pages.map((page) => (
          <Link
            key={page.title}
            href={translateRoute(`/${page.title}`)}
            className={classNames(
              "relative max-md:inline-flex items-center justify-between py-3 border-transparent border-b-4 ",
              page.component === component &&
                "md:border-b-4 md:border-b-publiq-blue max-md:font-semibold",
              afterStyles
            )}
          >
            <Heading level={5} className="max-md:text-xl">
              {t(`nav.${page.title}`)}
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
