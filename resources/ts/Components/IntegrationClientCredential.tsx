import { CopyText } from "./CopyText";
import React from "react";
import { useTranslation } from "react-i18next";
import { IntegrationType } from "../types/IntegrationType";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { Alert } from "./Alert";
import type { Integration } from "../types/Integration";
import type { AuthClient } from "../types/Credentials";

type Props = {
  client: {
    label: string;
    value: string | undefined;
  };
};

export const IntegrationClientCredential = ({ client }: Props) => {
  const { t } = useTranslation();
  const clientSecretLabel = t("details.credentials.client_secret");

  return (
    <div className="flex gap-1 max-md:flex-col max-md:items-start">
      <span className="flex items-center whitespace-nowrap">
        {t(client.label)}
      </span>
      {client.value && (
        <CopyText
          isSecret={t(client.label) === clientSecretLabel}
          text={client.value}
        />
      )}
    </div>
  );
};

export const IntegrationClientCredentials = ({
  client,
  status,
  type,
  isLive,
}: Pick<Integration, "status" | "type"> & {
  client: AuthClient | undefined;
  isLive: boolean;
}) => {
  const { t } = useTranslation();

  const clientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: client?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: client?.clientSecret,
    },
  ];

  return (
    <>
      {clientWithLabels.map((client) => (
        <IntegrationClientCredential
          key={`${client.label}-${client.value}`}
          client={client}
        />
      ))}
      {clientWithLabels.length &&
        !isLive &&
        type === IntegrationType.UiTPAS &&
        status !== IntegrationStatus.Active && (
          <Alert variant={"info"}>{t("details.credentials.waiting")}</Alert>
        )}
    </>
  );
};
