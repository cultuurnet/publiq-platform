import React from "react";
import { Link, InertiaLinkProps } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

type Props = InertiaLinkProps;

export const LinkButton = ({ children, className, ...props }: Props) => {
  return (
    <Link
      className={classNames(
        "relative inline-flex items-center justify-center bg-publiq-blue font-medium px-10 py-3 text-white group hover:bg-publiq-blue-light",
        className
      )}
      {...props}
    >
      <div className="absolute z-0 w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className="relative z-10">{children}</div>
    </Link>
  );
};
