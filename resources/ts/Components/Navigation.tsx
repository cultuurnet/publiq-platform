import type { ComponentProps } from "react";
import React from "react";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import type { InertiaLinkProps } from "@inertiajs/react";
import { Link as RouterLink, usePage } from "@inertiajs/react";
import { classNames } from "../utils/classNames";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { PubliqLogo } from "./logos/PubliqLogo";
import { externalLinkProps, isExternalLink } from "./Link";
import { useIsAuthenticated } from "../hooks/useIsAuthenticated";

type Page = {
  key: string;
  component?: string;
};

const Link = (props: ComponentProps<"a">) =>
  isExternalLink(props.href ?? "") ? (
    <a {...props} {...externalLinkProps} />
  ) : (
    <RouterLink {...(props as InertiaLinkProps)} />
  );

const allPages = [
  {
    component: "Integrations/Index",
    key: "integrations",
  },
  { key: "opportunities" },
  {
    key: "prices",
  },
  { key: "documentation" },
  { component: "Support/Index", key: "support" },
] as const;

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
  const isAuthenticated = useIsAuthenticated();

  const pages: Page[] = isAuthenticated
    ? allPages.filter((it) => ["integrations", "support"].includes(it.key))
    : allPages.filter((it) => it.key !== "integrations");

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
      <RouterLink href={translateRoute("/")}>
        <PubliqLogo color="publiq-blue" width={32} height={32} />
      </RouterLink>
      <div className="flex max-md:flex-col md:gap-8 min-w-[50%]">
        {pages.map((page) => {
          return (
            <Link
              key={page.key}
              href={
                "component" in page
                  ? translateRoute(`/${page.key}`)
                  : t(`nav.${page.key}.link`)
              }
              className={classNames(
                "relative max-md:inline-flex items-center justify-between py-3 border-transparent border-b-4 ",
                "component" in page &&
                  page.component === component &&
                  "md:border-b-4 md:border-b-publiq-blue max-md:font-semibold",
                afterStyles
              )}
            >
              <Heading level={5} className="max-md:text-xl">
                {t(`nav.${page.key}.label`)}
              </Heading>
              {orientation === "vertical" && (
                <FontAwesomeIcon icon={faChevronRight} />
              )}
            </Link>
          );
        })}
      </div>
    </section>
  );
}
