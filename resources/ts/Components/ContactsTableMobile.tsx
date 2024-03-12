import React, { ComponentProps } from "react";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div">;

export const ContactsTableMobile = ({ className, children }: Props) => {
  return (
    <div className={classNames("relative overflow-x-auto", className)}>
      <table className="w-full text-left border border-publiq-gray-lighter text-gray-500">
        {children}
      </table>
    </div>
  );
};
