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
        <div className="group relative inline-block">
          {children}
          <div
            className={classNames(
              "absolute left-full max-md:top-full top-2.5 max-md:left-1.5 z-20 ml-3 max-md:mt-3 -translate-y-1.5 max-md:-translate-x-1/2 whitespace-nowrap rounded bg-publiq-blue-dark py-[6px] px-4 text-sm font-semibold text-gray-100",
              visible ? "visible" : "hidden"
            )}
          >
            <span className="absolute left-[-3px] max-md:top-[1px] top-1/2 max-md:left-1/2 -z-10 h-2 w-2 -translate-y-1/2 max-md:-translate-x-1/2 rotate-45 rounded-sm bg-publiq-blue-dark"></span>
            {text}
          </div>
        </div>
      </div>
    </div>
  );
};
