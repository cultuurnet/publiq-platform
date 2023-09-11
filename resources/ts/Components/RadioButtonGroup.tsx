import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = {
  name: string;
  values: string[];
  value: string;
  onChange: (newValue: string) => void;
} & Omit<ComponentProps<"ul">, "onChange">;

export const RadioButtonGroup = ({
  name,
  values,
  value,
  onChange,
  className,
  ...props
}: Props) => {
  const getRoundedStyles = (index: number) => {
    switch (index) {
      case 0:
        return "rounded-l-lg";
      case 2:
        return "rounded-r-lg";
    }
  };

  return (
    <ul
      className={classNames("flex max-md:flex-col max-md:gap-1", className)}
      role="group"
      {...props}
    >
      {values.map((v, index) => (
        <li
          key={v}
          tabIndex={0}
          onClick={() => onChange(v)}
          onKeyUp={(e) => {
            if (e.key === "Enter") {
              onChange(v);
            }
          }}
          className={classNames(
            "px-4 py-2 text-sm font-medium text-center bg-white border border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-2 ring-publiq-blue-dark focus:bg-gray-100 max-sm:rounded-lg",
            getRoundedStyles(index),
            value === v ? "text-publiq-blue-dark" : "text-publiq-gray-dark"
          )}
        >
          <input type="radio" id={v} name={name} value={v} className="hidden" />
          <label htmlFor={v}>{v}</label>
        </li>
      ))}
    </ul>
  );
};
