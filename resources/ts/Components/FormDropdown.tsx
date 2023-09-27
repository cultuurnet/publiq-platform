import React, { ReactNode } from "react";
import { Heading } from "./Heading";

type Props = {
  children: React.ReactNode;
  title: string;
  actions: ReactNode;
};

export const FormDropdown = ({ title, children, actions }: Props) => {
  return (
    <div className="flex flex-col gap-4 max-md:px-5 px-10 py-5">
      <div className="flex gap-2 items-center">
        <Heading className="font-semibold" level={2}>
          {title}
        </Heading>
        {actions}
      </div>
        <div className="flex flex-col gap-6 py-4">{children}</div>
    </div>
  );
};
