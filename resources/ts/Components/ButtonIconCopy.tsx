import type { ComponentProps} from "react";
import React, { useState } from "react";
import { faCopy, faCheck } from "@fortawesome/free-solid-svg-icons";
import { classNames } from "../utils/classNames";
import { ButtonIcon } from "./ButtonIcon";

type Props = ComponentProps<"button">;

export const ButtonIconCopy = ({ onClick, className, ...props }: Props) => {
  const [isEnabled, setIsEnabled] = useState(false);

  const handleClick = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setIsEnabled(true);
    if (onClick) onClick(e);
    const timoutId = setTimeout(() => {
      setIsEnabled(false);
      clearTimeout(timoutId);
    }, 1000);
  };

  return (
    <ButtonIcon
      icon={isEnabled ? faCheck : faCopy}
      onClick={handleClick}
      className={classNames(
        "text-gray-600 transition-all ease-linear p-0 h-auto w-auto",
        isEnabled ? "text-green-600" : "",
        className
      )}
      {...props}
    ></ButtonIcon>
  );
};
