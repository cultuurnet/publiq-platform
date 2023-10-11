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
  active: "bg-status-green text-status-green-dark",
  blocked: "bg-status-red text-status-red-dark",
  deleted: "bg-status-red text-status-red-dark",
  draft: "bg-status-yellow text-status-pending-dark",
  pending_approval_integration: "bg-status-yellow text-status-yellow-dark",
  pending_approval_payment: "bg-status-yellow text-status-yellow-dark",
};

export const StatusLight = ({ status }: Props) => {
  const { t } = useTranslation();
  return (
    <div className="flex flex-col gap-3">
      <div
        className={classNames(
          "bg-publiq-blue-dark text-xs font-medium mr-2 px-2.5 py-0.5 rounded uppercase self-start",
          StatusToColor[status]
        )}
      >
        {t(`integrations.status.${status}`)}
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
