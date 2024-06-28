import React from "react";
import type { IconProp, SizeProp } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import type { ComponentProps } from "react";
import { classNames } from "../utils/classNames";
import { twMerge } from "tailwind-merge";

type Props = ComponentProps<"button"> & {
  icon: IconProp;
  color?: string;
  size?: SizeProp;
};

export const ButtonIcon = ({
  icon,
  color,
  size,
  className,
  ...props
}: Props) => {
  return (
    <button
      className={twMerge(
        classNames(
          "hover:bg-icon-gray-light focus:bg-icon-gray-dark group-focus:animate-pulse p-3 rounded-full grow-0 shrink-0 w-[2.8rem] h-[2.8rem] inline-flex items-center justify-center",
          size === "sm" && "w-[1.2rem] h-[1.2rem]",
          className
        )
      )}
      {...props}
    >
      <FontAwesomeIcon icon={icon} color={color} size={size} />
    </button>
  );
};
