import React from "react";
import type { ComponentProps } from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { ButtonIcon } from "./ButtonIcon";
import type {
  IconDefinition} from "@fortawesome/free-solid-svg-icons";
import {
  faXmark,
  faTriangleExclamation,
  faCircleCheck,
  faBell
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

type Variant = "error" | "success" | "info";

const variantToAlertStyle: Record<Variant, string> = {
  error: "bg-alert-error border border-alert-error-dark",
  success: "bg-alert-success border border-alert-success-dark",
  info: "bg-alert-info border border-alert-info-dark",
};

const variantToIcon: Record<Variant, IconDefinition> = {
  error: faTriangleExclamation,
  success: faCircleCheck,
  info: faBell,
};

const variantToIconColor: Record<Variant, string> = {
  error: "text-alert-error-dark",
  success: "text-alert-success-dark",
  info: "text-alert-info-dark",
};

const variantToHeadingStyle: Record<Variant, string> = {
  error: "text-publiq-gray",
  success: "text-publiq-gray",
  info: "text-publiq-gray",
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
        "flex gap-3 justify-between items-center rounded-md p-2",
        variantToAlertStyle[variant],
        className
      )}
    >
      <div className="flex gap-3 items-start pl-2">
        <FontAwesomeIcon
          icon={variantToIcon[variant]}
          className={variantToIconColor[variant]}
          size="lg"
        />
        <section className="flex flex-col">
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
          <div className="flex flex-col items-start text-sm max-w-2xl">
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
