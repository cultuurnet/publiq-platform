import type { ComponentProps } from "react";
import React from "react";
import { classNames } from "../utils/classNames";
import { twMerge } from "tailwind-merge";

type Props = ComponentProps<"div"> & {
  visible: boolean;
  text: string;
};

export const Tooltip = ({ visible, text, children, className }: Props) => {
  return (
    <div className={twMerge("w-[2.5rem]", className)}>
      <div>
        <div className="group relative inline-flex gap-2">
          {children}
          <div
            className={classNames(
              "absolute top-full left-0.5 mt-3 -translate-x-1/2 whitespace-nowrap rounded bg-publiq-blue-dark py-[6px] px-4 text-sm font-semibold text-gray-100",
              visible ? "visible" : "hidden"
            )}
          >
            <span className="absolute top-[1px] left-1/2 h-2 w-2 -translate-y-1/2 max-md:-translate-x-1/2 rotate-45 rounded-sm bg-publiq-blue-dark"></span>
            {text}
          </div>
        </div>
      </div>
    </div>
  );
};
