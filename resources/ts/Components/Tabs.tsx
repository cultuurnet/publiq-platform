import type { ComponentProps, ReactNode } from "react";
import React from "react";
import { classNames } from "../utils/classNames";

type OnTabChange = (tab: string) => void;

export type TabItem = {
  type: string;
  label: string;
  content: ReactNode;
};

type Props = {
  active: string;
  onChange: OnTabChange;
  items: TabItem[];
} & Omit<ComponentProps<"div">, "onChange" | "children">;

export const Tabs = ({ active, onChange, items, ...props }: Props) => {
  const activeItem = items.find((item) => item.type === active);

  return (
    <div className="flex flex-col gap-3 px-4" {...props}>
      <ul className="flex flex-wrap text-sm text-center text-gray-500 border-b border-gray-300">
        {items.map((item) => (
          <button
            key={item.type}
            onClick={() => onChange(item.type)}
            aria-current="page"
            className={classNames(
              "inline-block p-3",
              item.type === active &&
                "text-publiq-blue-light border-publiq-blue border-b-[3px]"
            )}
          >
            <li className="mr-2">{item.label}</li>
          </button>
        ))}
      </ul>
      <div className="flex flex-col gap-10 max-md:px-5 px-12 py-5">
        {activeItem?.content}
      </div>
    </div>
  );
};
