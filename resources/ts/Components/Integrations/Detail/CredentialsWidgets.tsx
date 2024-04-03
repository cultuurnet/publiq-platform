import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Credentials } from "./Credentials";
import type { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { OpenWidgetBuilderButton } from "../../IntegrationCard";
import { StatusLight } from "../../StatusLight";
import { ActivationFlow } from "../../ActivationFlow";

type Props = Pick<Integration, "id" | "status" | "subscription" | "type"> &
  Credentials & { email: string };

export const CredentialsWidgets = ({
  email,
  id,
  status,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  return (
    <div className="flex w-full max-lg:flex-col">
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("integrations.products.widgets")}
      </Heading>
      <div className="flex flex-col gap-6 min-w-[40rem]">
        {status !== IntegrationStatus.Active && (
          <div>
            <Heading className="font-semibold flex min-w-[5rem]" level={4}>
              {t("details.credentials.test")}
            </Heading>
            <OpenWidgetBuilderButton type={type} id={id} />
          </div>
        )}
        <div className="flex flex-col gap-3">
          <div>
            <Heading className="font-semibold flex min-w-[5rem]" level={4}>
              {t("details.credentials.live")}
            </Heading>
            <StatusLight status={status} />
          </div>
          <div>
            {status === IntegrationStatus.Active && (
              <OpenWidgetBuilderButton type={type} id={id} />
            )}
            {status === IntegrationStatus.Draft && (
              <ActivationFlow
                status={status}
                id={id}
                subscription={subscription}
                type={type}
                email={email}
              />
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
