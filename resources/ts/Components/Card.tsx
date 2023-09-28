import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";

type Props = {
  title: ReactElement | string;
  description?: string;
  img?: string;
  active?: boolean;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({
  title,
  description,
  img,
  active,
  children,
  className,
  ...props
}: Props) => {
  return (
    <div
      className={classNames(
        "w-full flex flex-col overflow-hidden shadow-lg px-6 py-6",
        img && "px-0 py-0 gap-10 max-lg:gap-3 p-0",
        active ? "bg-publiq-blue-dark bg-opacity-10" : "bg-white",
        className
      )}
      {...props}
    >
      {img && (
        <img
          src={img}
          className={classNames(
            img && "h-full w-auto aspect-square max-h-[12rem] object-contain"
          )}
        ></img>
      )}
      <div>
        <Heading level={2} className="font-medium mb-2">
          {title}
        </Heading>
        {description && (
          <p className="text-gray-700 text-base min-h-[5rem] break-words">
            {description}
          </p>
        )}
      </div>
      {children && <div className="flex flex-1">{children}</div>}
    </div>
  );
};
