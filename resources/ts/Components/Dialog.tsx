import type { ComponentProps, ReactNode } from "react";
import React from "react";
import { ButtonIcon } from "./ButtonIcon";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { createPortal } from "react-dom";

type Props = ComponentProps<"div"> & {
  isVisible?: boolean;
  onClose?: () => void;
  isFullscreen?: boolean;
  contentStyles?: string;
  title?: string;
  actions?: ReactNode;
};

export const Dialog = ({
  isVisible = false,
  isFullscreen = false,
  onClose,
  children,
  contentStyles,
  title,
  actions,
}: Props) => {
  if (!isVisible) {
    return null;
  }

  return createPortal(
    <>
      <div
        className={classNames(
          "fixed bg-white flex flex-col items-center z-[60] top-[50%] left-[50%] transform translate-x-[-50%] translate-y-[-50%] overflow-y-auto",
          isFullscreen
            ? "h-full w-full p-4"
            : "max-h-screen md:max-w-[40rem] md:min-w-[40rem] top-[30%]"
        )}
      >
        <div
          className={classNames(
            "w-full flex items-center justify-between px-6 py-2",
            !isFullscreen && "border-b border-gray-300"
          )}
        >
          <Heading level={3} className="font-semibold">
            {title}
          </Heading>
          <ButtonIcon
            icon={faXmark}
            onClick={onClose}
            size="lg"
            className="text-publiq-blue-dark self-end"
          />
        </div>

        <div
          className={classNames(
            "flex flex-col flex-1 w-full p-6 text-base font-light",
            contentStyles
          )}
        >
          {children}
        </div>
        {actions && (
          <div
            className={classNames(
              "w-full flex items-center gap-3 justify-end px-6 py-2",
              !isFullscreen && "border-t border-gray-300"
            )}
          >
            {actions}
          </div>
        )}
      </div>
      <div
        className={"fixed top-0 right-0 bg-black w-full h-full opacity-60 z-50"}
        onClick={onClose}
      />
    </>,
    document.body
  );
};

export type { Props as DialogProps };
