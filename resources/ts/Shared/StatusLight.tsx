import React, { ComponentProps } from "react";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { classNames } from "../utils/classNames";

type Props = ComponentProps<"div"> & {
  status: IntegrationStatus;
};

const StatusToColor: Record<IntegrationStatus, string> = {
  active: "bg-green-700",
  blocked: "bg-red-700",
  deleted: "bg-red-700",
  draft: "bg-orange-500",
  pending_approval_integration: "bg-orange-500",
  pending_approval_payment: "bg-orange-500",
};

export const StatusLight = ({ status }: Props) => {
  return (
    <div
      className={classNames(
        "w-[0.7rem] h-[0.7rem] rounded-full",
        StatusToColor[status]
      )}
    ></div>
  );
};
