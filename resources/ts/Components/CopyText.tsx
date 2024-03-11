import React, { ComponentProps, useRef, useState } from "react";
import { Tooltip } from "./Tooltip";
import { ButtonIconCopy } from "./ButtonIconCopy";
import { useTranslation } from "react-i18next";

export type Props = ComponentProps<"div">;

export const CopyText = ({ children }: Props) => {
  const { t } = useTranslation();

  const codeFieldRef = useRef<HTMLSpanElement>(null);

  const [isVisible, setIsVisible] = useState(false);

  const handleCopyToClipboard = () => {
    navigator.clipboard.writeText(codeFieldRef.current?.innerText ?? "");
    setIsVisible(true);
    const timeoutId = setTimeout(() => {
      setIsVisible(false);
      clearTimeout(timeoutId);
    }, 1000);
  };

  return (
    <div className="inline-flex gap-2 items-center bg-[#fdf3ef] rounded px-3 p-1">
      <span
        className=" text-ellipsis overflow-hidden whitespace-nowrap text-publiq-orange max-md:max-w-[15rem] max-xl:max-w-[30rem]"
        ref={codeFieldRef}
      >
        {children}
      </span>
      <Tooltip
        visible={isVisible}
        text={t("tooltip.copy")}
        className={"w-auto"}
      >
        <ButtonIconCopy
          onClick={handleCopyToClipboard}
          className={"text-publiq-orange"}
        />
      </Tooltip>
    </div>
  );
};
