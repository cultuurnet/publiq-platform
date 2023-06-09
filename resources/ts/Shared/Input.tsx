import React from "react";
import { ComponentProps } from "react";
import { classNames } from "../utils/classNames";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IconProp } from "@fortawesome/fontawesome-svg-core";

type Props = ComponentProps<"input"> & {
  iconBack?: IconProp;
};

export const Input = ({ children, className, iconBack, ...props }: Props) => {
  return (
    <div className={classNames("block relative w-full", className)}>
      <input
        className={classNames(
          "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
          !!iconBack && "pl-4 pr-9"
        )}
        {...props}
      >
        {children}
      </input>
      {iconBack && (
        <FontAwesomeIcon
          icon={iconBack}
          className="absolute right-3 top-[35%]"
        />
      )}
    </div>
  );
};
