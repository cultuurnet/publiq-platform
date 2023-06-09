import React, { ComponentProps } from "react";
import { IconButton } from "./IconButton";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div"> & {
  isVisible?: boolean;
  onClose?: () => void;
};

export const Dialog = ({ isVisible = false, onClose, children }: Props) => {
  if (!isVisible) {
    return null;
  }

  return (
    <>
      <div
        className={classNames(
          "flex flex-col fixed bg-publiq-gray-light z-[60] min-h-[12rem] top-[30%]",
          "left-[25%] right-[25%]", // Desktop
          "md:left-[10%] md:right-[10%]", // Tablet
          "max-md:left-[1rem] max-md:right-[1rem]" // Mobile
        )}
      >
        <div className="inline-flex w-full justify-end p-3">
          <IconButton icon={faXmark} onClick={onClose} />
        </div>
        <div className="flex flex-col flex-1 w-full p-4">{children}</div>
      </div>
      <div
        className={"fixed top-0 right-0 bg-black w-full h-full opacity-60 z-50"}
        onClick={onClose}
      />
    </>
  );
};

export type { Props as DialogProps };
