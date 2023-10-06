import React from "react";
import { Dialog } from "./Dialog";
import type { DialogProps } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { useTranslation } from "react-i18next";

type Props = DialogProps & {
  question: string;
  title: string;
  confirmLabel?: string;
  cancelLabel?: string;
  onConfirm: () => void;
  onCancel: () => void;
};

export const QuestionDialog = ({
  question,
  title,
  confirmLabel,
  cancelLabel,
  onConfirm,
  onCancel,
  ...props
}: Props) => {
  const { t } = useTranslation();

  const confirm = confirmLabel ?? t("dialog.confirm");
  const cancel = cancelLabel ?? t("dialog.cancel");

  return (
    <Dialog
      {...props}
      title={title}
      actions={
        <>
          <ButtonSecondary onClick={onCancel}>{cancel}</ButtonSecondary>
          <ButtonPrimary onClick={onConfirm}>{confirm}</ButtonPrimary>
        </>
      }
    >
      <p className="flex">{question}</p>
    </Dialog>
  );
};
