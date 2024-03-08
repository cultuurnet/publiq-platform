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
      <div className="flex flex-col gap-6 min-w-[40rem]">
        <div>
          <Heading className="font-semibold flex" level={4}>
            {t("details.credentials.test")}
          </Heading>
          <CopyText>{uiTiDv1TestConsumer?.apiKey}</CopyText>
        </div>
        <div>
          <Heading className="font-semibold" level={4}>
            {t("details.credentials.live")}
          </Heading>
          {status === "active" && (
            <CopyText>{uiTiDv1ProdConsumer?.apiKey}</CopyText>
          )}
          <div className="flex flex-col gap-3 align-center">
            {status !== "active" && (
              <StatusLight
                status={status}
              />
            )}
            {status === "draft" && auth0Clients.length === 0 && (
              <ActivationFlow
                status={status}
                id={id}
                subscription={subscription}
                type={type}
                email={email}
              />
            )}
            <Alert variant="info">{t("details.credentials.info")}</Alert>
          </div>
        </div>
      </div>
    </>
  );
};
