import React from "react";
import { ComponentProps } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { ButtonIcon } from "./ButtonIcon";
import {
  faXmark,
  faTriangleExclamation,
  faCircleCheck,
  faBell,
  IconDefinition,
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

type Variant = "error" | "success" | "info";

const variantToAlertStyle: Record<Variant, string> = {
  error: "bg-status-red border border-status-red-dark",
  success: "bg-status-green border border-status-green-medium",
  info: "bg-status-blue border border-status-blue-medium",
};

const variantToIcon: Record<Variant, IconDefinition> = {
  error: faTriangleExclamation,
  success: faCircleCheck,
  info: faBell,
};

const variantToIconColor: Record<Variant, string> = {
  error: "text-status-red-dark",
  success: "text-status-green-dark",
  info: "text-status-blue-dark",
};

const variantToHeadingStyle: Record<Variant, string> = {
  error: "text-status-red-dark",
  success: "text-status-green-dark",
  info: "text-status-blue-dark",
};

type Props = ComponentProps<"div"> & {
  visible?: boolean;
  closable?: boolean;
  onClose?: () => void;
  variant?: Variant;
  title?: string;
};

export const Alert = ({
  visible = true,
  closable = false,
  onClose,
  variant = "success",
  title,
  children,
  className,
  ...props
}: Props) => {
  if (!visible) {
    return null;
  }

  return (
    <div
      {...props}
      className={classNames(
        "flex gap-3 justify-between items-start rounded-md p-4",
        variantToAlertStyle[variant],
        className
      )}
    >
      <div className={"flex gap-3 items-start"}>
        <FontAwesomeIcon
          icon={variantToIcon[variant]}
          className={variantToIconColor[variant]}
          size="lg"
        />
        <section className={"flex flex-col"}>
          {title && (
            <Heading
              level={5}
              className={classNames(
                "text-base",
                variantToHeadingStyle[variant]
              )}
            >
              {title}
            </Heading>
          )}
          <div className={`flex flex-col items-start text-sm max-w-2xl`}>
            {children}
          </div>
        </section>
      </div>
      {closable && (
        <ButtonIcon
          icon={faXmark}
          size="lg"
          className="hover:bg-opacity-40"
          onClick={onClose}
        />
      )}
    </div>
  );
};
