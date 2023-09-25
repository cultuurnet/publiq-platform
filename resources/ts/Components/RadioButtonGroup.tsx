import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Option = {
  label: string;
  value: string;
};

type Props = {
  name: string;
  options: Option[];
  value: string;
  onChange: (newValue: string) => void;
} & Omit<ComponentProps<"ul">, "onChange">;

export const RadioButtonGroup = ({
  name,
  options,
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

  const optionClasses = (index: number, option: Option) => {
    return classNames(
      "px-4 py-2 text-base font-medium text-center bg-white border border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-2 ring-publiq-blue-dark focus:bg-gray-100 max-sm:rounded-lg",
      getRoundedStyles(index),
      value === option.value ? "text-publiq-blue-dark" : "text-publiq-gray-dark"
    );
  };

  return (
    <ul
      className={classNames("flex max-md:flex-col max-md:gap-1", className)}
      role="group"
      {...props}
    >
      {options.map((option, index) => (
        <li
          key={option.value}
          tabIndex={0}
          onClick={() => onChange(option.value)}
          onKeyUp={(e) => {
            if (e.key === "Enter") {
              onChange(option.value);
            }
          }}
          className={classNames(optionClasses(index, option), 'px-10 py-3 max-md:px-5 max-md:py-2 w-[100%] h-[100%]')}
        >
          <input
            type="radio"
            id={option.value}
            name={name}
            value={option.value}
            className="hidden"
          />
          <label htmlFor={option.value}>{option.label}</label>
        </li>
      ))}
    </ul>
  );
};
