import type { ComponentProps, ReactElement } from "react";
import React from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { Link } from "@inertiajs/react";

type Props = {
  title?: ReactElement | string;
  description?: string;
  img?: ReactElement | string;
  badge?: string;
  icon?: ReactElement;
  active?: boolean;
  headless?: boolean;
  contentStyles?: string;
  textCenter?: boolean;
  border?: boolean;
  iconButton?: ReactElement;
  clickableHeading?: boolean;
  id?: string;
  headingStyles?: string;
} & Omit<ComponentProps<"div">, "title">;

export const Card = ({
  title,
  description,
  img,
  badge = "",
  icon,
  iconButton,
  active,
  children,
  className,
  contentStyles,
  border = false,
  headless = false,
  textCenter,
  clickableHeading = false,
  headingStyles,
  id,
  ...props
}: Props) => {
  const translateRoute = useTranslateRoute();
  const backgroundColor = active
    ? "bg-status-green-medium bg-opacity-10"
    : "bg-white";

  const heading =
    typeof title !== "string" ? (
      title
    ) : (
      <Heading
        level={2}
        className={classNames(
          "font-medium max-sm:text-basis",
          clickableHeading && "text-publiq-blue-dark hover:underline",
          headingStyles
        )}
      >
        {title}
      </Heading>
    );

  const header = (
    <div
      className={classNames(
        "flex justify-between",
        border &&
          `${!headless ? "border-b border-publiq-gray-300 max-sm:px-2 p-5" : "py-3"}`
      )}
    >
      <div className={`flex items-center gap-3 ${headless ? "h-16" : "h-11"}`}>
        {icon}
        {clickableHeading ? (
          <Link href={`${translateRoute("/integrations")}/${id}`}>
            {heading}
          </Link>
        ) : (
          heading
        )}
        {!!badge && (
          <span className="text-publiq-gray-400 bg-publiq-gray-50 uppercase border border-publiq-gray-400 text-xs font-medium  mr-2 px-2.5 py-0.5 rounded">
            {badge.replaceAll("-", " ")}
          </span>
        )}
      </div>
      {iconButton && <div className="justify-self-end">{iconButton}</div>}
    </div>
  );

  return (
    <div
      className={classNames(
        "w-full flex flex-col overflow-visible drop-shadow-card relative",
        img && "px-0 py-0 max-lg:gap-3 p-0",
        !headless ? backgroundColor : "",
        !border && "px-6 py-6",
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
        {img && <span className={"h-28"}>{img}</span>}
      </div>
      <div
        className={classNames(
          "flex flex-col",
          textCenter && "text-center items-center",
          !border && "gap-5"
        )}
      >
        {(title || icon || badge || iconButton) && header}
        {description && (
          <p
            className={classNames(
              "text-gray-700 text-base min-h-[5rem] break-words",
              backgroundColor
            )}
          >
            {description}
          </p>
        )}
      </div>
      {children && (
        <div className={classNames(backgroundColor, contentStyles)}>
          {children}
        </div>
      )}
    </div>
  );
};
