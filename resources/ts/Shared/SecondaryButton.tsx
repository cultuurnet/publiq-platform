import React from "react";
import { Link, InertiaLinkProps } from "@inertiajs/react";

type Props = InertiaLinkProps;

export const SecondaryButton = ({ children, ...props }: Props) => {
  return (
    <Link
      className="relative inline-flex items-center justify-center px-10 py-3 font-medium outline outline-1 outline-publiq-blue text-publiq-blue group
      hover:bg-publiq-blue-dark hover:bg-opacity-10
    "
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue"></div>
      <div className="relative z-10">{children}</div>
    </Link>
  );
};
