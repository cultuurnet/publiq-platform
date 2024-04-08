import React from "react";
import type { InertiaLinkProps } from "@inertiajs/react";
import { Link } from "@inertiajs/react";
import { classNames } from "../utils/classNames";

type Props = { contentStyles?: string } & InertiaLinkProps;

export const ButtonLink = ({
  children,
  className,
  contentStyles,
  ...props
}: Props) => {
  return (
    <Link
      className={classNames(
        "relative inline-flex items-center justify-center bg-publiq-blue-dark font-light px-7 py-2 max-md:px-5 text-white group hover:brightness-125",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className={classNames("relative z-10", contentStyles)}>
        {children}
      </div>
    </Link>
  );
};
