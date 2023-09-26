import React, { ComponentProps, ReactElement, cloneElement } from "react";
import { classNames } from "../utils/classNames";

type OnTabChange = (tab: string) => void;

type ItemProps = { type: string; label: string; onChange?: OnTabChange } & Omit<
  ComponentProps<"li">,
  "onChange"
>;

const Item = ({ type, label, className, onChange }: ItemProps) => {
  return (
    <li className="mr-2">
      <button
        onClick={() => onChange!(type)}
        aria-current="page"
        className={classNames("inline-block p-4 rounded-t-lg", className)}
      >
        {label}
      </button>
    </li>
  );
};

type Props = {
  active: string;
  onChange: OnTabChange;
} & Omit<ComponentProps<"div">, "onChange">;

export const Tabs = ({ children, active, onChange, ...props }: Props) => {
  const tabItems = React.Children.toArray(children).filter(
    (child) => (child as ReactElement).type === Item
  ) as ReactElement[];

  const styledTabItems = tabItems.map((item) => {
    const isActive = item.props.type === active;

    if (!isActive) {
      return cloneElement(item, { ...item.props, onChange });
    }

    return cloneElement(item, {
      ...item.props,
      className: classNames(
        item.props.className,
        "text-publiq-blue-dark bg-gray-100"
      ),
      onChange,
    });
  });

  const tabContent = tabItems.find((tabItem) => tabItem.props.type === active)
    ?.props.children;

  return (
    <div className="flex flex-col gap-3" {...props}>
      <ul className="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200">
        {styledTabItems}
      </ul>
      {tabContent}
    </div>
  );
};

Tabs.Item = Item;
