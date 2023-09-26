import React, { ComponentProps } from "react";
import { ButtonIcon } from "./ButtonIcon";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div"> & {
  isVisible?: boolean;
  onClose?: () => void;
  isFullscreen?: boolean;
};

export const Dialog = ({
  isVisible = false,
  isFullscreen = false,
  onClose,
  children,
  className,
}: Props) => {
  if (!isVisible) {
    return null;
  }

  return (
    <>
      <div
        className={classNames(
          "fixed bg-white flex flex-col items-center z-[60] top-[50%] left-[50%] transform translate-x-[-50%] translate-y-[-50%] rounded-lg",
          isFullscreen
            ? "h-full w-full p-4"
            : "min-h-[14rem] max-md:w-[90%] md:min-w-[40rem] top-[30%] p-4"
        )}
      >
        <ButtonIcon
          icon={faXmark}
          onClick={onClose}
          size="lg"
          className="text-publiq-blue-dark self-end"
        />

        <div
          className={classNames(
            "flex flex-col flex-1 w-full p-4 text-xl",
            className
          )}
        >
          {children}
        </div>
      </div>
      <div
        className={"fixed top-0 right-0 bg-black w-full h-full opacity-60 z-50"}
        onClick={onClose}
      />
    </>
  );
};

export type { Props as DialogProps };
