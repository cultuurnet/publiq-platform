import React from "react";
import { Trans, useTranslation } from "react-i18next";
import { Link } from "./Link";
import { ButtonPrimary } from "./ButtonPrimary";
import { router } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { Integration } from "../Pages/Integrations/Index";

type Props = { id: Integration["id"] };

export const ActivationRequest = ({ id }: Props) => {
  const { t } = useTranslation();

  const translateRoute = useTranslateRoute();
  const handleRedirect = () =>
    router.get(
      `${translateRoute("/integrations")}/${id}?tab=credentials&isDialogVisible=true`
    );

  return (
    <div className="flex flex-col gap-3">
      <div>
        <Trans
          i18nKey="integrations.status.pending_approval_integration_description"
          t={t}
          components={[
            <Link
              href={t("integrations.status.before_going_live_link")}
              className="text-publiq-blue-dark hover:underline"
            />,
          ]}
        />
      </div>
      <ButtonPrimary className="self-start" onClick={handleRedirect}>
        {t("integrations.status.activate")}
      </ButtonPrimary>
    </div>
  );
};
