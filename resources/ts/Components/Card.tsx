import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";

type Props = {
  title: ReactElement | string;
  description?: string;
  img?: string;
  badge?: string;
  active?: boolean;
  contentStyles?: string;
  textCenter?: boolean;
  border?: boolean;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({
  title,
  description,
  img,
  badge = "",
  active,
  children,
  className,
  contentStyles,
  border = false,
  textCenter,
  ...props
}: Props) => {
  return (
    <div
      className={classNames(
        "w-full flex flex-col overflow-hidden shadow-lg",
        img && "px-0 py-0 gap-10 max-lg:gap-3 p-0",
        active ? "bg-status-green-medium bg-opacity-10" : "bg-white",
        border ? "border border-publiq-gray-dark" : "px-6 py-6",
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
          "flex flex-col",
          textCenter && "text-center",
          !border && "gap-5"
        )}
      >
        <div
          className={classNames(
            "flex gap-3 items-center",
            border && "border-b border-publiq-gray-dark px-10 py-2"
          )}
        >
          <Heading level={2} className="font-medium mb-2">
            {title}
          </Heading>
          {!!badge && (
            <span className="bg-publiq-blue-dark text-white text-xs font-medium  mr-2 px-2.5 py-0.5 rounded">
              {badge}
            </span>
          )}
        </div>

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
