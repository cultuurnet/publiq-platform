import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";

type Props = {
  title: ReactElement | string;
  description?: string;
  img?: string;
  active?: boolean;
  contentStyles?: string;
  textCenter?: boolean;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({
  title,
  description,
  img,
  active,
  children,
  className,
  contentStyles,
  textCenter,
  ...props
}: Props) => {
  return (
    <div
      className={classNames(
        "w-full flex flex-col overflow-hidden shadow-lg px-6 py-6",
        img && "px-0 py-0 gap-10 max-lg:gap-3 p-0",
        active ? "bg-status-green-medium bg-opacity-10" : "bg-white",
        className
      )}
      {...props}
    >
      <div className="relative flex justify-center">
        {active && (
          <FontAwesomeIcon
            icon={faCheck}
            size="xl"
            className="text-green-500 absolute top-0 left-0"
          />
        )}
        {img && (
          <img
            src={img}
            className={classNames(
              img && "h-full w-auto aspect-square max-h-[12rem] object-contain"
            )}
          ></img>
        )}
      </div>

      <div
        className={classNames(
          "flex flex-col gap-5",
          textCenter && "text-center"
        )}
      >
        <Heading level={2} className="font-medium mb-2">
          {title}
        </Heading>
        {description && (
          <p className="text-gray-700 text-base min-h-[5rem] break-words">
            {description}
          </p>
        )}
        {children && <div className={contentStyles}>{children}</div>}
      </div>
    </div>
  );
};
