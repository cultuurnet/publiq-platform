import type { ComponentPropsWithoutRef, Ref } from "react";
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

export const Link = ({
  children,
  href,
  className,
  hasExternalIcon = false,
  ...props
}: LinkProps) => {
  const isExternal = !href.startsWith("/") && !href.startsWith("#");

  return (
    <>
      {isExternal ? (
        <a
          className={classNames("inline-flex gap-2 items-center", className)}
          href={href}
          target="_blank"
          rel="noreferrer"
          {...props}
        >
          <span className="hover:underline">{children}</span>
          {hasExternalIcon && (
            <FontAwesomeIcon icon={faArrowUpRightFromSquare} />
          )}
        </a>
      ) : (
        <InertiaLink
          className={classNames(
            "text-publiq-blue inline-flex gap-2 items-center",
            className
          )}
          href={href}
          {...props}
        >
          <span className="hover:underline">{children}</span>
        </InertiaLink>
      )}
    </>
  );
};
