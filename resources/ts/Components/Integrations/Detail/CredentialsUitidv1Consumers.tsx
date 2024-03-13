import React, { useMemo } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import {
  Auth0Client,
  Integration,
  UiTiDv1Consumer,
} from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";
import { Alert } from "../../Alert";
import { OpenWidgetBuilderButton } from "../../IntegrationCard";
import { IntegrationType } from "../../../types/IntegrationType";
import { IntegrationStatus } from "../../../types/IntegrationStatus";

type Props = {
  uiTiDv1Consumers: UiTiDv1Consumer[];
  auth0Clients: Auth0Client[];
  email: string;
  id: Integration["id"];
  status: Integration["status"];
  subscription: Integration["subscription"];
  type: Integration["type"];
};

export const CredentialsUitidv1Consumers = ({
  uiTiDv1Consumers,
  auth0Clients,
  id,
  status,
  email,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  const uiTiDv1TestConsumer = useMemo(
    () => uiTiDv1Consumers.find((consumer) => consumer.environment === "test"),
    [uiTiDv1Consumers]
  );

  const uiTiDv1ProdConsumer = useMemo(
    () => uiTiDv1Consumers.find((consumer) => consumer.environment === "prod"),
    [uiTiDv1Consumers]
  );

  return (
    <>
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v1")}
      </Heading>
      <div className="flex flex-col gap-4 min-w-[40rem]">
        {type !== IntegrationType.Widgets && (
          <>
            <Heading className="font-semibold" level={4}>
              {t("details.credentials.test")}
            </Heading>
            <CopyText>{uiTiDv1TestConsumer?.apiKey}</CopyText>
          </>
        )}

        {status !== IntegrationStatus.Active &&
          type === IntegrationType.Widgets && (
            <>
              <Heading className="font-semibold" level={4}>
                {t("details.credentials.test")}
              </Heading>
              <OpenWidgetBuilderButton
                type={type}
                id={uiTiDv1TestConsumer!.apiKey}
              />
            </>
          )}
        <div className="flex flex-col gap-2">
          <Heading className="font-semibold" level={4}>
            {t("details.credentials.live")}
          </Heading>
          <StatusLight status={status} />
          {uiTiDv1ProdConsumer && (
            <div className="flex flex-col gap-2">
              {status === IntegrationStatus.Active &&
                type !== IntegrationType.Widgets && (
                  <CopyText>{uiTiDv1ProdConsumer?.apiKey}</CopyText>
                )}
              {status === IntegrationStatus.Active &&
                type === IntegrationType.Widgets && (
                  <OpenWidgetBuilderButton
                    type={type}
                    id={uiTiDv1ProdConsumer.apiKey}
                  />
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
    </>
  );
};
