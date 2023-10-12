import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"button">;

export const ButtonPrimary = ({ children, className, ...props }: Props) => {
  return (
    <button
      className={classNames(
        "relative inline-flex items-center justify-center rounded bg-publiq-blue-dark font-medium px-7 py-2 max-md:px-5 text-white group hover:hover:brightness-125",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className="relative z-10">{children}</div>
    </button>
  );
};
