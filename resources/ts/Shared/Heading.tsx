import React, { ReactNode } from 'react';

type HeadingProps = {
  children: ReactNode;
};

const levelToHeadingComponent = {
  1: ({ children: text }: HeadingProps) => <h1 className="text-4xl">{text}</h1>,
  2: ({ children: text }: HeadingProps) => <h2 className="text-2xl">{text}</h2>,
  3: ({ children: text }: HeadingProps) => <h3 className="text-xl">{text}</h3>,
  4: ({ children: text }: HeadingProps) => <h4 className="text-lg">{text}</h4>,
  5: ({ children: text }: HeadingProps) => (
    <h5 className="text-base">{text}</h5>
  ),
  6: ({ children: text }: HeadingProps) => <h6 className="text-sm">{text}</h6>,
} as const;

type Props = {
  level: keyof typeof levelToHeadingComponent;
  children: ReactNode;
};

export const Heading = ({ level, children }: Props) => {
  const HeadingComponent = levelToHeadingComponent[level];

  return <HeadingComponent>{children}</HeadingComponent>;
};
