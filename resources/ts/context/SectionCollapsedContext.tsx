import React, { ReactNode, createContext, useContext, useState } from "react";

type SectionCollapsed = {
  integrationsSettings: boolean;
  contacts: boolean;
  billing: boolean;
};

const defaultContext = {
  integrationsSettings: true,
  contacts: true,
  billing: true,
};

export const SectionCollapsedContext = createContext([
  undefined as unknown as SectionCollapsed,
  undefined as unknown as React.Dispatch<
    React.SetStateAction<SectionCollapsed>
  >,
] as const);

export const SectionCollapsedProvider = ({
  children,
}: {
  children: ReactNode;
}) => {
  const pair = useState<SectionCollapsed>(defaultContext);

  return (
    <SectionCollapsedContext.Provider value={pair}>
      {children}
    </SectionCollapsedContext.Provider>
  );
};

export const useSectionCollapsedContext = () =>
  useContext(SectionCollapsedContext);
