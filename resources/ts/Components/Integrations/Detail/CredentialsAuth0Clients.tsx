import React, { useMemo } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import {
  Auth0Client,
  Integration,
  UiTiDv1Consumer,
} from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
//import { ButtonPrimary } from "../../ButtonPrimary";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";
import { OpenWidgetBuilderButton } from "../../IntegrationCard";
import { IntegrationType } from "../../../types/IntegrationType";
import { IntegrationStatus } from "../../../types/IntegrationStatus";

type Props = {
  auth0Clients: Auth0Client[];
  uiTiDv1Consumers: UiTiDv1Consumer[];
  email: string;
  id: Integration["id"];
  status: Integration["status"];
  subscription: Integration["subscription"];
  type: Integration["type"];
};

export const CredentialsAuth0Clients = ({
  auth0Clients,
  id,
  status,
  email,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  const auth0TestClient = useMemo(
    () => auth0Clients.find((client) => client.tenant === "test"),
    [auth0Clients]
  );
  const auth0ProdClient = useMemo(
    () => auth0Clients.find((client) => client.tenant === "prod"),
    [auth0Clients]
  );

  const auth0TestClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: auth0TestClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: auth0TestClient?.clientSecret,
    },
  ];

  const auth0ProdClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: auth0ProdClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: auth0ProdClient?.clientSecret,
    },
  ];

  return (
    <>
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v2")}
      </Heading>
      {/*{!auth0ProdClient ? (
        <div className="flex flex-col flex-1 gap-4">
          <p>{t("details.credentials.uitid_alert")}</p>
          <ButtonPrimary
            className="self-start"
          >
            {t("details.credentials.action_uitid")}
          </ButtonPrimary>
        </div>
      ) : ( */}
      <div className="flex flex-col flex-1 gap-4">
        {type !== IntegrationType.Widgets && (
          <div className="flex flex-col gap-3">
            <Heading className="font-semibold flex min-w-[5rem]" level={4}>
              {t("details.credentials.test")}
            </Heading>
            {auth0TestClientWithLabels.map((client) => (
              <div
                key={`${client.label}-${client.value}`}
                className="flex gap-1 max-md:flex-col max-md:items-start"
              >
                <span className="flex items-center whitespace-nowrap">
                  {t(client.label)}
                </span>
                <CopyText>{client.value}</CopyText>
              </div>
            ))}
          </div>
        )}
        {status !== IntegrationStatus.Active &&
          type === IntegrationType.Widgets && (
            <>
              <Heading className="font-semibold flex min-w-[5rem]" level={4}>
                {t("details.credentials.test")}
              </Heading>
              <OpenWidgetBuilderButton
                type={type}
                id={auth0TestClient!.clientId}
              />
            </>
          )}

        <div className="flex flex-col gap-2">
          <Heading className="font-semibold min-w-[5rem]" level={4}>
            {t("details.credentials.live")}
          </Heading>
          <StatusLight status={status} />
          {status === IntegrationStatus.Active &&
            type !== IntegrationType.Widgets && (
              <div className="flex flex-col gap-3">
                {auth0ProdClientWithLabels.map((client) => (
                  <div
                    key={`${client.label}-${client.value}`}
                    className="flex gap-1 max-md:flex-col max-md:items-start"
                  >
                    <span className="flex items-center whitespace-nowrap">
                      {t(client.label)}
                    </span>
                    <CopyText>{client.value}</CopyText>
                  </div>
                ))}
              </div>
            )}
          {status === IntegrationStatus.Active &&
            type === IntegrationType.Widgets && (
              <OpenWidgetBuilderButton
                id={auth0ProdClient!.clientId}
                type={type}
              />
            )}
          <div className="flex flex-col gap-3 align-center">
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
      {/* )} */}
    </>
  );
};
