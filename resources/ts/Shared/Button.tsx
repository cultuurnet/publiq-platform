import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"button">;

export const Button = ({ children, className, ...props }: Props) => {
  return (
    <button
      className={classNames(
        "relative inline-flex items-center justify-center bg-publiq-blue font-medium px-10 py-3 text-white group hover:bg-publiq-blue-light",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-blue text-white"></div>
      <div className="relative z-10">{children}</div>
    </button>
  );
};
