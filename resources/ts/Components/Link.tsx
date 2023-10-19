import React, { ComponentProps } from "react";
import { Link as InertiaLink, InertiaLinkProps } from "@inertiajs/react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowUpRightFromSquare } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";

type Props = InertiaLinkProps & ComponentProps<"a">;

export const Link = ({ children, href, className, ...props }: Props) => {
  const isExternal = !href.startsWith("/") && !href.startsWith("#");

  return (
    <>
      {isExternal ? (
        <a
          className={classNames(
            "text-publiq-blue inline-flex gap-2 items-center",
            className
          )}
          href={href}
          target="_blank"
          rel="noreferrer"
          {...props}
        >
          <span className="hover:underline">{children}</span>
          <FontAwesomeIcon icon={faArrowUpRightFromSquare} />
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
