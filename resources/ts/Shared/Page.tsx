import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"section">;

export const Page = ({ children, className, ...props }: Props) => {
  return (
    <section
      className={classNames(
        "flex flex-col items-center w-full pt-6 px-4 :max-sm:px-2 gap-7 md:min-w-[40rem] max-w-7xl",
        className
      )}
      {...props}
    >
      {children}
    </section>
  );
};
