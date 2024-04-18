import type { ComponentProps, ElementType, ReactElement } from "react";
import React from "react";
import { classNames } from "../utils/classNames";

export const RadioButtonGroupLabel = ({
  name,
  description,
}: {
  name: string | ReactElement;
  description: string | ReactElement;
}) => (
  <div className="flex flex-col flex-grow justify-between md:flex-row items-center gap-2">
    <span>{name}</span>
    <span className="text-neutral-700 font-thin max-md:text-center text-sm">
      {description}
    </span>
  </div>
);

type Option = {
  label: string | ReactElement;
  value: string;
};

type Props = {
  name: string;
  options: Option[];
  value: string;
  onChange: (newValue: string) => void;
  orientation: "vertical" | "horizontal";
} & Omit<ComponentProps<"ul">, "onChange">;

export const RadioButtonGroup = ({
  name,
  options,
  value,
  onChange,
  className,
  orientation = "horizontal",
  ...props
}: Props) => {
  const isVertical = orientation === "vertical";
  const getRoundedStyles = (index: number) => {
    if (options.length === 1) return "rounded-lg";

    switch (index) {
      case 0:
        return isVertical ? "rounded-t-lg" : "rounded-l-lg";
      case options.length - 1:
        return isVertical ? "rounded-b-lg" : "rounded-r-lg";
    }
  };

  const optionClasses = (index: number, option: Option) => {
    return classNames(
      "px-4 py-2 text-base font-medium border border-gray-200 hover:bg-gray-100 focus:z-10 focus:ring-2 ring-publiq-blue-dark focus:bg-gray-100 max-sm:rounded-lg cursor-pointer",
      getRoundedStyles(index),
      value === option.value
        ? "bg-publiq-gray-75 text-publiq-blue-dark"
        : "bg-white text-publiq-gray-900"
    );
  };

  return (
    <ul
      className={classNames(
        "flex max-md:gap-1",
        isVertical ? "flex-col" : "flex-row max-md:flex-col",
        className
      )}
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
          className={classNames(
            optionClasses(index, option),
            "flex items-center gap-5 px-10 py-3 max-md:px-5 max-md:py-2 w-[100%] h-[100%]"
          )}
        >
          <input
            type="radio"
            id={option.value}
            name={name}
            value={option.value}
            checked={value === option.value}
          />
          <div className="flex flex-grow">
            <label
              htmlFor={option.value}
              className="flex flex-grow cursor-pointer"
            >
              {option.label}
            </label>
          </div>
        </li>
      ))}
    </ul>
  );
};
