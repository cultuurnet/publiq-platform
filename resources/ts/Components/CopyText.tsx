import React, { useRef, useState } from "react";
import { Tooltip } from "./Tooltip";
import { ButtonIconCopy } from "./ButtonIconCopy";
import { useTranslation } from "react-i18next";
import { faEye, faEyeSlash } from "@fortawesome/free-solid-svg-icons";
import { ButtonIcon } from "./ButtonIcon";

type Props = { text: string; isSecret?: boolean };

export const CopyText = ({ text, isSecret }: Props) => {
  const { t } = useTranslation();

  const codeFieldRef = useRef<HTMLSpanElement>(null);

  const [isVisible, setIsVisible] = useState(false);
  const [isSecretVisible, setIsSecretVisible] = useState(false);

  const handleCopyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text ?? "");
    setIsVisible(true);
    const timeoutId = setTimeout(() => {
      setIsVisible(false);
      clearTimeout(timeoutId);
    }, 1000);
  };

  return (
    <div className="inline-flex gap-2 items-center bg-[#fdf3ef] rounded px-3 p-1">
      <span
        className="font-mono whitespace-pre	text-ellipsis overflow-hidden text-sm text-publiq-orange max-md:max-w-[13rem] max-xl:max-w-[28rem]"
        ref={codeFieldRef}
      >
        {!isSecret || isSecretVisible ? text : "•".repeat(text?.length ?? 36)}
      </span>
      <Tooltip visible={isVisible} text={t("tooltip.copy")} className="w-auto">
        <ButtonIconCopy
          onClick={() => handleCopyToClipboard(text)}
          className="text-publiq-orange"
        />
        {isSecret && (
          <ButtonIcon
            icon={isSecretVisible ? faEyeSlash : faEye}
            className="text-publiq-orange p-0 h-auto w-auto"
            onClick={() => {
              setIsSecretVisible((prev) => !prev);
            }}
          />
        )}
      </Tooltip>
    </div>
  );
};
