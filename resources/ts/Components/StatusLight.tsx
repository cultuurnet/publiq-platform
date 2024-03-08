import React, { ComponentProps, useState } from "react";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { router } from "@inertiajs/react";
import { ActivationDialog } from "./ActivationDialog";
import { Subscription } from "../Pages/Integrations/Index";
import { Values } from "../types/Values";
import { IntegrationType } from "../types/IntegrationType";
import { ActivationRequest } from "./ActivationRequest";

type Props = ComponentProps<"div"> & {
  status: IntegrationStatus;
  id: string;
  subscription?: Subscription;
  type: Values<typeof IntegrationType>;
  email?: string;
};

const StatusToColor: Record<IntegrationStatus, string> = {
  [IntegrationStatus.Active]: "bg-[#6bcd69]",
  [IntegrationStatus.Blocked]: "bg-[#3b3b3b]",
  [IntegrationStatus.Deleted]: "bg-[#dd5242]",
  [IntegrationStatus.Draft]: "bg-[#dcdcdc]",
  [IntegrationStatus.PendingApprovalIntegration]: "bg-[#e69336]",
  [IntegrationStatus.PendingApprovalPayment]: "bg-[#e69336]",
};

export const StatusLight = ({
  status,
  id,
  subscription,
  type,
  email,
}: Props) => {
  const { t } = useTranslation();

  const url = new URL(document.location.href);
  const isDialogVisible = url.searchParams.get("isDialogVisible");

  const [isActivationDialogVisible, setIsActivationDialogVisible] = useState(
    !!isDialogVisible ?? false
  );

  return (
    <div className="flex flex-col gap-3">
      <div className="flex flex-row items-center">
        <div
          className={classNames(
            "h-3 w-3 rounded-full mr-2",
            StatusToColor[status]
          )}
        />
        <div className={"mr-2 self-start"}>
          {t(`integrations.status.${status}`)}
        </div>
      </div>
      {(status === "pending_approval_integration" ||
        status === "pending_approval_payment") && (
        <span>{t("details.credentials.in_progress")}</span>
      )}
    </div>
  );
};
