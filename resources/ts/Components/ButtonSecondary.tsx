import type { ComponentProps } from "react";
import React from "react";

import { classNames } from "../utils/classNames";

type ButtonVariant = "default" | "danger";

type Props = {
  orientation?: string;
  variant?: ButtonVariant;
} & ComponentProps<"button">;

export const ButtonSecondary = ({
  children,
  className,
  variant = "default",
  ...props
}: Props) => {
  return (
    <button
      className={classNames(
        "relative inline-flex items-center justify-center px-7 py-2 max-md:px-5 font-light border hover:bg-opacity-10",
        variant === "default" &&
          "border-publiq-blue text-publiq-blue hover:bg-publiq-blue-dark",
        variant === "danger" &&
          "border-status-red-dark text-status-red-dark hover:bg-status-red",
        className
      )}
      {...props}
    >
      <div className="absolute w-[50%] h-[50%] opacity-0 group-focus:animate-pulse bg-publiq-gray-50"></div>
      <div className="relative z-10 flex items-center gap-2">{children}</div>
    </button>
  );
};

export type { Props as ButtonSecondaryProps };
