import React, { useMemo } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import {
  Auth0Client,
  Integration,
  UiTiDv1Consumer,
} from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { ButtonPrimary } from "../../ButtonPrimary";
import { router } from "@inertiajs/react";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";

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
  uiTiDv1Consumers,
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

  const handleDistributeAuth0Clients = () => {
    router.post(`/integrations/${id}/auth0-clients`);
  };

  return (
    <>
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v2")}
      </Heading>
      {uiTiDv1Consumers.length > 0 && !auth0ProdClient ? (
        <div className="flex flex-col flex-1 gap-4">
          <p>{t("details.credentials.uitid_alert")}</p>
          <ButtonPrimary
            className="self-start"
            onClick={handleDistributeAuth0Clients}
          >
            {t("details.credentials.action_uitid")}
          </ButtonPrimary>
        </div>
      ) : (
        <div className="flex flex-col flex-1 gap-4">
          <div className="flex flex-col">
            <Heading className="font-semibold flex min-w-[5rem]" level={4}>
              {t("details.credentials.test")}
            </Heading>
            <div className="flex flex-col gap-3">
              {auth0TestClientWithLabels.map((client) => (
                <div className="flex gap-1 max-md:flex-col max-md:items-start">
                  <span className="flex items-center whitespace-nowrap">
                    {t(client.label)}
                  </span>
                  <CopyText>{client.value}</CopyText>
                </div>
              ))}
            </div>
          </div>
          <div className="flex flex-col">
            <Heading className="font-semibold min-w-[5rem]" level={4}>
              {t("details.credentials.live")}
            </Heading>
            {status === "active" && (
              <div className="flex flex-col gap-3">
                {auth0ProdClientWithLabels.map((client) => (
                  <div className="flex gap-1 max-md:flex-col max-md:items-start">
                    <span className="flex items-center whitespace-nowrap">
                      {t(client.label)}
                    </span>
                    <CopyText>{client.value}</CopyText>
                  </div>
                ))}
              </div>
            )}
            <div className="flex flex-col gap-3 align-center">
              {status !== "active" && (
                <StatusLight
                  status={status}
                  id={id}
                  subscription={subscription}
                  type={type}
                  email={email}
                />
              )}
              {status === "draft" && (
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
      )}
    </>
  );
};
