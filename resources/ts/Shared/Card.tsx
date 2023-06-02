import React, { ComponentProps } from "react";

type Props = {
  title: string;
  description: string;
} & ComponentProps<"div">;

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
