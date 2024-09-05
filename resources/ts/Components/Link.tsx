import type {
  ComponentPropsWithoutRef,
  HTMLAttributeAnchorTarget,
  Ref,
} from "react";
import React from "react";
import type { InertiaLinkProps } from "@inertiajs/react";
import { Link as InertiaLink } from "@inertiajs/react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowUpRightFromSquare } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";

export type LinkProps = InertiaLinkProps &
  ComponentPropsWithoutRef<"a"> & {
    ref?: Ref<HTMLAnchorElement>;
    hasExternalIcon?: boolean;
  };

type ExternalLinkProps = {
  target: HTMLAttributeAnchorTarget;
  rel: string;
};

export const externalLinkProps: ExternalLinkProps = {
  target: "_blank",
  rel: "noopener",
};

export const isExternalLink = (href: string) =>
  !href.startsWith("/") && !href.startsWith("#");
export const Link = ({
  children,
  href,
  className,
  hasExternalIcon = false,
  ...props
}: LinkProps) => {
  const isExternal = isExternalLink(href);

  return (
    <>
      {isExternal ? (
        <a
          className={className}
          href={href}
          target="_blank"
          rel="noreferrer"
          {...props}
        >
          <span className="hover:underline">{children}</span>
          {hasExternalIcon && (
            <FontAwesomeIcon icon={faArrowUpRightFromSquare} className="mx-1" />
          )}
        </a>
      ) : (
        <InertiaLink
          className={classNames(
            "text-publiq-blue inline-flex gap-2 items-baseline",
            className
          )}
          href={href}
          {...props}
        >
          <span className="hover:underline flex items-baseline gap-1">
            {children}
          </span>
        </InertiaLink>
      )}
    </>
  );
};
