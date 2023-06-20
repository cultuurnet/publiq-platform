import React from "react";
import { IconProp } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { classNames } from "../utils/classNames";
import { InertiaLinkProps, Link } from "@inertiajs/react";

type Props = InertiaLinkProps & {
  icon: IconProp;
  color?: string;
};

export const IconLink = ({ icon, color, className, ...props }: Props) => {
  return (
    <Link
      className={classNames(
        "bg-publiq-gray-medium hover:bg-gray-200 group-focus:animate-pulse p-3 rounded-full inline-flex items-center",
        className
      )}
      {...props}
    >
      <FontAwesomeIcon icon={icon} color={color} />
    </Link>
  );
};
