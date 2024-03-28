import type { ComponentProps, ReactElement } from "react";
import React, { cloneElement, useMemo } from "react";
import { classNames } from "../utils/classNames";

type OnTabChange = (tab: string) => void;

type ItemProps = { type: string; label: string; onChange?: OnTabChange } & Omit<
  ComponentProps<"li">,
  "onChange"
>;

const Item = ({ type, label, className, onChange }: ItemProps) => {
  return (
    <button
      onClick={() => onChange!(type)}
      aria-current="page"
      className={classNames("inline-block p-3", className)}
    >
      <li className="mr-2">{label}</li>
    </button>
  );
};

type Props = {
  active: string;
  onChange: OnTabChange;
} & Omit<ComponentProps<"div">, "onChange">;

export const Tabs = ({ children, active, onChange, ...props }: Props) => {
  const tabItems = useMemo(
    () =>
      React.Children.toArray(children).filter(
        (child) =>
          typeof child === "object" && "type" in child && child.type === Item
      ) as ReactElement[],
    [children]
  );

  const styledTabItems = useMemo(
    () =>
      tabItems.map((item) => {
        const isActive = item.props.type === active;

        if (!isActive) {
          return cloneElement(item, { ...item.props, onChange });
        }

        return cloneElement(item, {
          ...item.props,
          className: classNames(
            item.props.className,
            "text-publiq-blue-light border-publiq-blue border-b-[3px]"
          ),
          onChange,
        });
      }),
    [active, onChange, tabItems]
  );

  const tabContent = useMemo(
    () =>
      tabItems.find((tabItem) => tabItem.props.type === active)?.props.children,
    [active, tabItems]
  );

  return (
    <div className="flex flex-col gap-3 px-4" {...props}>
      <ul className="flex flex-wrap text-sm text-center text-gray-500 border-b border-gray-300">
        {styledTabItems}
      </ul>
      <div className="flex flex-col gap-10 max-md:px-5 px-12 py-5">
        {tabContent}
      </div>
    </div>
  );
};

Tabs.Item = Item;
