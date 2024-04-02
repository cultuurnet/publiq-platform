import type { ComponentProps } from "react";
import React from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div">;

export const ContactsTableDesktop = ({ className, children }: Props) => {
  return (
    <div className={classNames("relative", className)}>
      <table className="w-full text-left border border-publiq-gray-50 text-gray-500 min-w-[40rem]">
        {children}
      </table>
    </div>
  );
};
