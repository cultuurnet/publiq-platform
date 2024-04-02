import React from "react";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";
import type { LinkProps } from "./Link";

type Props = LinkProps;

export const ButtonLinkSecondary = ({
  children,
  href,
  className,
  ...props
}: Props) => {
  const isExternal = !href.startsWith("/") && !href.startsWith("#");

  return (
    <>
      {isExternal ? (
        <a
          className={classNames(
            "relative inline-flex items-center justify-center px-7 py-2 max-md:px-5 font-light border border-publiq-blue text-publiq-blue group hover:bg-publiq-blue-dark hover:bg-opacity-10",
            className
          )}
          href={href}
          target="_blank"
          rel="noreferrer"
          {...props}
        >
          <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-gray-50"></div>
          <div className="relative z-10 flex items-center gap-2">
            {children}
          </div>
        </a>
      ) : (
        <Link
          className={classNames(
            "relative inline-flex items-center justify-center px-7 py-2 max-md:px-5 font-light border border-publiq-blue text-publiq-blue group hover:bg-publiq-blue-dark hover:bg-opacity-10",
            className
          )}
          href={href}
          {...props}
        >
          <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-gray-50"></div>
          <div className="relative z-10 flex items-center gap-2">
            {children}
          </div>
        </Link>
      )}
    </>
  );
};

export type { Props as ButtonLinkSecondaryProps };
