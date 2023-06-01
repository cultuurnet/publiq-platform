import React, { memo, useMemo } from "react";
import type { ComponentProps } from "react";

const levelToHeadingComponent = {
  1: ({ children, ...rest }: ComponentProps<"h1">) => (
    <h1 className="text-4xl" {...rest}>
      {children}
    </h1>
  ),
  2: ({ children, ...rest }: ComponentProps<"h2">) => (
    <h2 className="text-2xl" {...rest}>
      {children}
    </h2>
  ),
  3: ({ children, ...rest }: ComponentProps<"h3">) => (
    <h3 className="text-xl" {...rest}>
      {children}
    </h3>
  ),
  4: ({ children, ...rest }: ComponentProps<"h4">) => (
    <h4 className="text-lg" {...rest}>
      {children}
    </h4>
  ),
  5: ({ children, ...rest }: ComponentProps<"h5">) => (
    <h5 className="text-base" {...rest}>
      {children}
    </h5>
  ),
  6: ({ children, ...rest }: ComponentProps<"h6">) => (
    <h6 className="text-sm" {...rest}>
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
