import React from "react";

type Props = {
  children: React.ReactNode;
};

export const TabLayout = ({ children }: Props) => {
  return (
    <div className="flex flex-col gap-10 max-md:px-5 px-12 py-5">
      {children}
    </div>
  );
};
