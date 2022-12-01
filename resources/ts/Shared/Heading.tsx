import React, { memo, ReactNode, useMemo } from 'react';

type HeadingProps = {
  children: ReactNode;
};

const levelToHeadingComponent = {
  1: ({ children }: HeadingProps) => <h1 className="text-4xl">{children}</h1>,
  2: ({ children }: HeadingProps) => <h2 className="text-2xl">{children}</h2>,
  3: ({ children }: HeadingProps) => <h3 className="text-xl">{children}</h3>,
  4: ({ children }: HeadingProps) => <h4 className="text-lg">{children}</h4>,
  5: ({ children }: HeadingProps) => <h5 className="text-base">{children}</h5>,
  6: ({ children }: HeadingProps) => <h6 className="text-sm">{children}</h6>,
} as const;

type Props = {
  level: keyof typeof levelToHeadingComponent;
  children: ReactNode;
};

export const Heading = memo(({ level, children }: Props) => {
  const HeadingComponent = useMemo(
    () => levelToHeadingComponent[level],
    [level],
  );

  return <HeadingComponent>{children}</HeadingComponent>;
});

Heading.displayName = 'Heading';
