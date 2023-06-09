import React, { ComponentProps } from "react";

import { classNames } from "../utils/classNames";

type Props = {
  orientation?: string;
} & ComponentProps<"button">;

export const ButtonSecondary = ({ children, className, ...props }: Props) => {
  return (
    <button
      className={classNames(
        "relative inline-flex items-center justify-center px-10 py-3 max-md:px-5 max-md:py-2 font-medium outline outline-1 outline-publiq-blue text-publiq-blue group hover:bg-publiq-blue-dark hover:bg-opacity-10",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-gray-light"></div>
      <div className="relative z-10">{children}</div>
    </button>
  );
};

export type { Props as ButtonSecondaryProps };
