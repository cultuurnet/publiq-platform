import type { ComponentProps } from "react";
import React from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"section">;

export const Page = ({ children, className, ...props }: Props) => {
  return (
    <section
      className={classNames(
        "flex flex-col items-center w-full px-4 max-md:px-2 gap-7 md:min-w-[40rem] max-w-6xl min-h-screen pb-8 mb-16 font-light tracking-wide",
        className
      )}
      {...props}
    >
      {children}
    </section>
  );
};
