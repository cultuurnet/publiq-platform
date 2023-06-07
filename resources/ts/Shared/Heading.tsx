import React, { memo, useMemo } from "react";
import type { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

const levelToHeadingComponent = {
  1: ({ children, className, ...rest }: ComponentProps<"h1">) => (
    <h1 className={classNames("text-4xl", className)} {...rest}>
      {children}
    </h1>
  ),
  2: ({ children, className, ...rest }: ComponentProps<"h2">) => (
    <h2 className={classNames("text-2xl", className)} {...rest}>
      {children}
    </h2>
  ),
  3: ({ children, className, ...rest }: ComponentProps<"h3">) => (
    <h3 className={classNames("text-xl", className)} {...rest}>
      {children}
    </h3>
  ),
  4: ({ children, className, ...rest }: ComponentProps<"h4">) => (
    <h4 className={classNames("text-lg", className)} {...rest}>
      {children}
    </h4>
  ),
  5: ({ children, className, ...rest }: ComponentProps<"h5">) => (
    <h5 className={classNames("text-base", className)} {...rest}>
      {children}
    </h5>
  ),
  6: ({ children, className, ...rest }: ComponentProps<"h6">) => (
    <h6 className={classNames("text-sm", className)} {...rest}>
      {children}
    </h6>
  ),
} as const;

type Props = {
  level: keyof typeof levelToHeadingComponent;
} & ComponentProps<"h1" | "h2" | "h3" | "h4" | "h5" | "h6">;

export const Heading = memo(({ level, children, ...props }: Props) => {
  const HeadingComponent = useMemo(
    () => levelToHeadingComponent[level],
    [level]
  );

  return <HeadingComponent {...props}>{children}</HeadingComponent>;
});

Heading.displayName = "Heading";
