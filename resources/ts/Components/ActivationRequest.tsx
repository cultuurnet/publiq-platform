import React from "react";
import { Trans, useTranslation } from "react-i18next";
import { Link } from "./Link";
import { ButtonPrimary } from "./ButtonPrimary";
import { router } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { IntegrationType } from "../types/IntegrationType";
import type { Integration } from "../types/Integration";

type Props = Pick<Integration, "id" | "type">;

export const ActivationRequest = ({ id, type }: Props) => {
  const { t } = useTranslation();

  const translateRoute = useTranslateRoute();
  const handleActivate = () =>
    router.get(
      `${translateRoute("/integrations")}/${id}?tab=credentials&isDialogVisible=true`
    );

  return (
    <div className="flex flex-col">
      <div>
        {type === IntegrationType.EntryApi && (
          <Trans
            i18nKey="integrations.status.pending_approval_integration_description"
            t={t}
            components={[
              <Link
                key={t(
                  "integrations.status.pending_approval_integration_description"
                )}
                href={t("integrations.status.before_going_live_link")}
                className="text-publiq-blue-dark hover:underline"
              />,
            ]}
          />
        )}
      </div>
      <ButtonPrimary className="self-start" onClick={handleActivate}>
        {t("integrations.status.activate")}
      </ButtonPrimary>
    </div>
  );
};
