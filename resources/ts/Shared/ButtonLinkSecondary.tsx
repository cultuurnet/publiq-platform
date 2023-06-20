import React from "react";
import { Link, InertiaLinkProps } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

type Props = InertiaLinkProps;

export const ButtonLinkSecondary = ({
  children,
  className,
  ...props
}: Props) => {
  return (
    <Link
      className={classNames(
        "relative inline-flex items-center justify-center px-10 py-3 max-md:px-5 max-md:py-2 font-medium outline outline-1 outline-publiq-blue text-publiq-blue group hover:bg-publiq-blue-dark hover:bg-opacity-10",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-gray-light"></div>
      <div className="relative z-10 flex items-center gap-2">{children}</div>
    </Link>
  );
};

export type { Props as ButtonLinkSecondaryProps };
