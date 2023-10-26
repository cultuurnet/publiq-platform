import React from "react";
import { Dialog } from "./Dialog";
import type { DialogProps } from "./Dialog";
import { ButtonPrimary } from "./ButtonPrimary";
import { useTranslation } from "react-i18next";

type Props = DialogProps & {
  info: string;
  title: string;
  confirmLabel?: string;
  onConfirm: () => void;
};

export const InformationDialog = ({
  info,
  title,
  confirmLabel,
  onConfirm,
  ...props
}: Props) => {
  const { t } = useTranslation();

  const confirm = confirmLabel ?? t("dialog.close");

  return (
    <Dialog
      {...props}
      title={title}
      actions={
        <>
          <ButtonPrimary onClick={onConfirm}>{confirm}</ButtonPrimary>
        </>
      }
    >
      <p className="flex">{info}</p>
    </Dialog>
  );
};
