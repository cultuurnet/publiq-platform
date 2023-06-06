import React from "react";
import { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"input">;

export const Input = ({ children, className, ...props }: Props) => {
  return (
    <input
      className={classNames(
        "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
        className
      )}
      {...props}
    >
      {children}
    </input>
  );
};
