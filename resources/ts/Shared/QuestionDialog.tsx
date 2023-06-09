import React from "react";
import { Dialog } from "./Dialog";
import type { DialogProps } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";

type Props = DialogProps & {
  question: string;
  onConfirm: () => void;
  onCancel: () => void;
};

export const QuestionDialog = ({
  question,
  onConfirm,
  onCancel,
  children,
  ...props
}: Props) => {
  return (
    <Dialog {...props}>
      <p className="flex flex-1">{question}</p>
      <div className="flex flex-1">{children}</div>
      <div className="self-end w-full inline-flex gap-3 justify-end border-t-publiq-gray-medium">
        <ButtonSecondary onClick={onCancel}>No</ButtonSecondary>
        <ButtonPrimary onClick={onConfirm}>Yes</ButtonPrimary>
      </div>
    </Dialog>
  );
};
