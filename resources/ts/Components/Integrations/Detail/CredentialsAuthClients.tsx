import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
//import { ButtonPrimary } from "../../ButtonPrimary";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { Credentials } from "./Credentials";

type Props = Pick<Integration, "id" | "status" | "subscription" | "type"> &
  Credentials & { email: string };

export const CredentialsAuthClients = ({
  testClient,
  prodClient,
  id,
  status,
  email,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  const auth0TestClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: testClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: testClient?.clientSecret,
    },
  ];

  const auth0ProdClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: prodClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: prodClient?.clientSecret,
    },
  ];

  return (
    <div className="flex w-full max-lg:flex-col gap-6">
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
        <div className="flex flex-col gap-2">
          <Heading className="font-semibold min-w-[5rem]" level={4}>
            {t("details.credentials.live")}
          </Heading>
          <StatusLight status={status} />
          {status === IntegrationStatus.Active && (
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
    </div>
  );
};
