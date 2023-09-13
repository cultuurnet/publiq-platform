import React from "react";
import { Link, InertiaLinkProps } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

type Props = InertiaLinkProps;

export const ButtonLink = ({ children, className, ...props }: Props) => {
  return (
    <Link
      className={classNames(
        "relative inline-flex items-center justify-center rounded bg-publiq-blue font-medium px-10 py-3 max-md:px-5 max-md:py-2 text-white group hover:bg-publiq-blue-light",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className="relative z-10">{children}</div>
    </Link>
  );
};
