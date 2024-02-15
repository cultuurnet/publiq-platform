import React, { ComponentProps, ReactElement } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { Link } from "@inertiajs/react";

type Props = {
  title: ReactElement | string;
  description?: string;
  img?: ReactElement | string;
  badge?: string;
  icon?: ReactElement | string;
  active?: boolean;
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
  textCenter,
  clickableHeading = false,
  headingStyles,
  id,
  ...props
}: Props) => {
  const translateRoute = useTranslateRoute();

  return (
    <div
      className={classNames(
        "w-full flex flex-col overflow-hidden drop-shadow-card",
        img && "px-0 py-0 max-lg:gap-3 p-0",
        active
          ? "bg-status-green-medium bg-opacity-10"
          : "bg-publiq-gray-light",
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
          textCenter && "text-center",
          !border && "gap-5 items-center"
        )}
      >
        <div
          className={classNames(
            "flex justify-between",
            border && "border-b border-publiq-gray-300 max-sm:px-2 px-7 py-5"
          )}
        >
          <div className="flex items-center gap-3 h-16">
            {icon}
            {clickableHeading ? (
              <Link href={`${translateRoute("/integrations")}/${id}`}>
                <Heading
                  level={2}
                  className={classNames(
                    "font-medium text-publiq-blue-dark hover:underline max-sm:text-basis",
                    headingStyles
                  )}
                >
                  {title}
                </Heading>
              </Link>
            ) : (
              <Heading
                level={2}
                className={classNames(
                  "font-medium max-sm:text-basis",
                  headingStyles
                )}
              >
                {title}
              </Heading>
            )}
            {!!badge && (
              <span className=" text-publiq-gray-medium-dark bg-publiq-gray-light uppercase border border-publiq-gray-medium-dark text-xs font-medium  mr-2 px-2.5 py-0.5 rounded">
                {badge.replaceAll("-", " ")}
              </span>
            )}
          </div>
          {iconButton && <div className="justify-self-end">{iconButton}</div>}
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
