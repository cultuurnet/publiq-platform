import React from "react";
import { Dialog } from "./Dialog";
import type { DialogProps } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { useTranslation } from "react-i18next";

type Props = DialogProps & {
  question: string;
  confirmLabel?: string;
  cancelLabel?: string;
  onConfirm: () => void;
  onCancel: () => void;
};

export const QuestionDialog = ({
  question,
  confirmLabel,
  cancelLabel,
  onConfirm,
  onCancel,
  children,
  ...props
}: Props) => {
  const { t } = useTranslation();

  const confirm = confirmLabel ?? t("dialog.confirm");
  const cancel = cancelLabel ?? t("dialog.cancel");

  return (
    <Dialog {...props}>
      <p className="flex">{question}</p>
      <div className="flex p-5">{children}</div>
      <div className="self-end w-full inline-flex gap-3 justify-end border-t-publiq-gray-medium">
        <ButtonSecondary onClick={onCancel}>{cancel}</ButtonSecondary>
        <ButtonPrimary onClick={onConfirm}>{confirm}</ButtonPrimary>
      </div>
    </Dialog>
  );
};
