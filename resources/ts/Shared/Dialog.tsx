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
}: Props) => {
  if (!isVisible) {
    return null;
  }

  return (
    <>
      <div
        className={classNames(
          "flex flex-col fixed bg-publiq-gray-light z-[60] p-5",
          isFullscreen
            ? "left-[1rem] right-[1rem] top-[1rem] bottom-[1rem]"
            : "min-h-[14rem] max-md:w-[90%] md:min-w-[40rem] top-[30%]"
        )}
      >
        <ButtonIcon
          icon={faXmark}
          onClick={onClose}
          size="lg"
          className="text-publiq-blue-dark self-end"
        />

        <div className="flex flex-col flex-1 w-full p-4 text-xl">
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
