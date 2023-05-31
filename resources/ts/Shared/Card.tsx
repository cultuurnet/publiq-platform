import React, { ComponentProps } from "react";

type Props = {
  title: string;
  description: string;
} & ComponentProps<"div">;

export const Card = ({ title, description, children }: Props) => {
  return (
    <div className="max-w-sm rounded overflow-hidden shadow-lg">
      <div className="px-6 py-4">
        <div className="font-bold text-xl mb-2">{title}</div>
        <p className="text-gray-700 text-base">{description}</p>
      </div>
      {children && <div className="px-6 py-4">{children}</div>}
    </div>
  );
};
