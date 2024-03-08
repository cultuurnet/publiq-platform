import React, { ComponentProps } from "react";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "./ButtonPrimary";
import { Link } from "@inertiajs/react";

type Props = ComponentProps<"div"> & {
  status: IntegrationStatus;
};

const StatusToColor: Record<IntegrationStatus, string> = {
  [IntegrationStatus.Active]: "bg-[#6bcd69]",
  [IntegrationStatus.Blocked]: "bg-[#3b3b3b]",
  [IntegrationStatus.Deleted]: "bg-[#dd5242]",
  [IntegrationStatus.Draft]: "bg-[#dcdcdc]",
  [IntegrationStatus.PendingApprovalIntegration]: "bg-[#e69336]",
  [IntegrationStatus.PendingApprovalPayment]: "bg-[#e69336]",
};

export const StatusLight = ({ status }: Props) => {
  const { t } = useTranslation();
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
      {(status === "pending_approval_payment" ||
        status === "pending_approval_integration") && (
        <div className="flex flex-col gap-3">
          <div>
            {status === "pending_approval_integration" ? (
              <span>
                {t(
                  "integrations.status.pending_approval_integration_description"
                )}
              </span>
            ) : (
              <>
                <span>
                  {t(
                    "integrations.status.pending_approval_payment_description"
                  )}
                </span>
                <Link className="text-publiq-blue-dark" href="#">
                  {" "}
                  {t("integrations.status.here")}
                </Link>
              </>
            )}
          </div>
          <ButtonPrimary className="self-start">
            {t("integrations.status.activate")}
          </ButtonPrimary>
        </div>
      )}
    </div>
  );
};
