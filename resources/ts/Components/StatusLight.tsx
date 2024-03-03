import React, { ComponentProps, useState } from "react";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { classNames } from "../utils/classNames";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "./ButtonPrimary";
import { Link, router } from "@inertiajs/react";
import { ActivationDialog } from "./ActivationDialog";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { Subscription } from "../Pages/Integrations/Index";
import { Values } from "../types/Values";
import { IntegrationType } from "../types/IntegrationType";

type Props = ComponentProps<"div"> & {
  status: IntegrationStatus;
  id: string;
  subscription?: Subscription;
  type: Values<typeof IntegrationType>;
  email: string;
};

const StatusToColor: Record<IntegrationStatus, string> = {
  active: "bg-status-green text-status-green-dark",
  blocked: "bg-status-red text-status-red-dark",
  deleted: "bg-status-red text-status-red-dark",
  draft: "bg-status-yellow text-status-pending-dark",
  pending_approval_integration: "bg-status-yellow text-status-yellow-dark",
  pending_approval_payment: "bg-status-yellow text-status-yellow-dark",
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

  const translateRoute = useTranslateRoute();

  const handleRedirect = () =>
    router.get(
      `${translateRoute("/integrations")}/${id}?tab=credentials&isDialogVisible=true`
    );

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
      {status === "pending_approval_integration" && (
        <span>{t("details.credentials.in_progress")}</span>
      )}
      {status === "draft" && (
        <div className="flex flex-col gap-3">
          <div>
            <>
              <span>{t("details.credentials.status_alert")}</span>
              <Link className="text-publiq-blue-dark" href="#">
                {" "}
                {t("integrations.status.here")}
              </Link>
            </>
          </div>
          <ButtonPrimary className="self-start" onClick={handleRedirect}>
            {t("integrations.status.activate")}
          </ButtonPrimary>
          <ActivationDialog
            isVisible={isActivationDialogVisible}
            onClose={() => {
              router.get(url.toString(), {
                isDialogVisible: undefined,
              });
              setIsActivationDialogVisible(false);
            }}
            id={id}
            subscription={subscription}
            type={type}
            email={email}
          />
        </div>
      )}
    </div>
  );
};
