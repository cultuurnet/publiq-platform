import React from "react";
import { Link, InertiaLinkProps } from "@inertiajs/react";

type Props = InertiaLinkProps;

export const LinkButton = ({ children, ...props }: Props) => {
  return (
    <Link
      className="relative bg-publiq-blue font-medium px-10 py-3 text-white group
       hover:bg-publiq-blue-light"
      {...props}
    >
      <div className="absolute z-0 w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className="relative z-10">{children}</div>
    </Link>
  );
};
