import React from "react";
import { IconProp } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"button"> & {
  icon: IconProp;
  color?: string;
};

export const IconButton = ({ icon, color, className, ...props }: Props) => {
  return (
    <button
      className={classNames(
        "bg-publiq-gray-medium hover:bg-gray-200 group-focus:animate-pulse p-3 rounded-full inline-flex items-center",
        className
      )}
      {...props}
    >
      <FontAwesomeIcon icon={icon} color={color} />
    </button>
  );
};
