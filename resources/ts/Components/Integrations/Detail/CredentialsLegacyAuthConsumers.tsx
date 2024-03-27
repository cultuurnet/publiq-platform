import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";
import { Alert } from "../../Alert";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { Credentials } from "./Credentials";

type Props = Pick<Integration, "id" | "status" | "subscription" | "type"> &
  Credentials & { email: string };

export const CredentialsLegacyAuthConsumers = ({
  legacyTestConsumer,
  legacyProdConsumer,
  id,
  status,
  email,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  return (
    <div className="flex w-full max-lg:flex-col gap-6 border-b pb-10 border-gray-300">
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v1")}
      </Heading>
      <div className="flex flex-col gap-4 min-w-[40rem]">
        <Heading className="font-semibold" level={4}>
          {t("details.credentials.test")}
        </Heading>
        <div className="flex gap-1 max-md:flex-col max-md:items-start">
          <span className="flex items-center whitespace-nowrap">
            {t("details.credentials.api_key")}
          </span>
          {legacyTestConsumer && (
            <CopyText>{legacyTestConsumer.apiKey}</CopyText>
          )}
        </div>
        <div className="flex flex-col gap-2">
          <Heading className="font-semibold" level={4}>
            {t("details.credentials.live")}
          </Heading>
          <StatusLight status={status} />
          {legacyProdConsumer && (
            <div className="flex flex-col gap-2">
              {status === IntegrationStatus.Active && (
                <div className="flex gap-1 max-md:flex-col max-md:items-start">
                  <span className="flex items-center whitespace-nowrap">
                    {t("details.credentials.api_key")}
                  </span>
                  <CopyText>{legacyProdConsumer.apiKey}</CopyText>
                </div>
              )}

              <Alert variant="info">{t("details.credentials.info")}</Alert>
            </div>
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
  );
};
