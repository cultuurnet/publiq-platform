import React from "react";
import type { ComponentProps } from "react";
import { classNames } from "../utils/classNames";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import type { IconProp } from "@fortawesome/fontawesome-svg-core";

type Props = ComponentProps<"input"> & {
  iconBack?: IconProp;
  inputId?: string;
};

export const Input = ({
  children,
  className,
  iconBack,
  disabled,
  inputId,
  ...props
}: Props) => {
  return (
    <div className={classNames("block relative w-full", className)}>
      <input
        className={classNames(
          "appearance-none block w-full bg-gray-100 text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500 rounded-lg",
          !!iconBack && "pl-4 pr-9",
          !disabled && "bg-white border-gray-200 outline-none"
        )}
        disabled={disabled}
        id={inputId}
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
