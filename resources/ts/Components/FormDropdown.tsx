import React from "react";
import { Heading } from "./Heading";

type Props = {
  children: React.ReactNode;
  title: string;
};

export const FormDropdown = ({ title, children }: Props) => {
  return (
    <div className="w-full flex max-lg:flex-col items-start max-lg:gap-4 lg:gap-48 max-md:px-5 px-10 py-5">
      <div className="flex gap-2 items-center">
        <Heading className="font-semibold" level={3}>
          {title}
        </Heading>
      </div>
      <div className="w-full flex flex-col gap-6">{children}</div>
    </div>
  );
};
