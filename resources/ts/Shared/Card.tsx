import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";

type Props = {
  title: ReactElement | string;
  description: string;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({ title, description, children, className }: Props) => {
  return (
    <div
      className={classNames(
        "flex flex-col gap-10 rounded overflow-hidden shadow-lg bg-white px-6 py-8",
        className
      )}
    >
      <div>
        <div className="font-bold text-xl mb-2">{title}</div>
        <p className="text-gray-700 text-base min-h-[5rem]">{description}</p>
      </div>
      {children && <div className="flex flex-1">{children}</div>}
    </div>
  );
};
