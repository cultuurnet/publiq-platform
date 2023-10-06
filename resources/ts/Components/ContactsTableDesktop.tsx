import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div">;

export const ContactsTableDesktop = ({ className, children }: Props) => {
  return (
    <div className={classNames("relative overflow-x-scroll", className)}>
      <table className="relative w-full text-left border border-publiq-gray-light text-gray-500">
        {children}
      </table>
    </div>
  );
};
