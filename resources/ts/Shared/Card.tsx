import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";

type Props = {
  title: ReactElement | string;
  description: string;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({ title, description, children }: Props) => {
  return (
    <div className="flex flex-col gap-10 max-w-sm rounded overflow-hidden shadow-lg bg-white px-6 py-8">
      <div>
        <div className="font-bold text-xl mb-2">{title}</div>
        <p className="text-gray-700 text-base">{description}</p>
      </div>
      {children && <div>{children}</div>}
    </div>
  );
};
