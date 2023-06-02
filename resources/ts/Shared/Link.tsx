import React from "react";
import { Link as InertiaLink, InertiaLinkProps } from "@inertiajs/react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowUpRightFromSquare } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";
type Props = InertiaLinkProps;

export const Link = ({ children, href, className, ...props }: Props) => {
  const isExternal = !href.startsWith("/") && !href.startsWith("#");
  
  return (
    <InertiaLink
      className={classNames(
        "text-publiq-blue inline-flex gap-2 items-center",
        className
      )}
      href={href}
      {...props}
    >
      <span className="hover:underline">{children}</span>
      {isExternal && <FontAwesomeIcon icon={faArrowUpRightFromSquare} />}
    </InertiaLink>
  );
};
