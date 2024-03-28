import React from "react";
import type { IconProp } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { classNames } from "../utils/classNames";
import type { InertiaLinkProps } from "@inertiajs/react";
import { Link } from "@inertiajs/react";

type Props = InertiaLinkProps & {
  icon: IconProp;
  color?: string;
};

export const IconLink = ({ icon, color, className, ...props }: Props) => {
  return (
    <Link
      className={classNames(
        "bg-publiq-gray-100 hover:bg-gray-200 group-focus:animate-pulse p-3 rounded-full inline-flex items-center",
        className
      )}
      {...props}
    >
      <FontAwesomeIcon icon={icon} color={color} />
    </Link>
  );
};
