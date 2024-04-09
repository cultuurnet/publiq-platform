import React, { useMemo } from "react";
import { CredentialsLegacyAuthConsumers } from "./CredentialsLegacyAuthConsumers";
import { CredentialsAuthClients } from "./CredentialsAuthClients";
import { IntegrationType } from "../../../types/IntegrationType";
import { CredentialsWidgets } from "./CredentialsWidgets";
import type {
  AuthClient,
  LegacyAuthConsumer,
} from "../../../types/Credentials";
import type { Integration } from "../../../types/Integration";
import { Alert } from "../../Alert";
import { useTranslation } from "react-i18next";

type Props = Integration & {
  email: string;
};

export type Credentials = {
  testClient?: AuthClient;
  prodClient?: AuthClient;
  legacyTestConsumer?: LegacyAuthConsumer;
  legacyProdConsumer?: LegacyAuthConsumer;
};

export const Credentials = ({
  id,
  status,
  email,
  subscription,
  type,
  keyVisibility,
  legacyAuthConsumers,
  authClients,
}: Props) => {
  const { t } = useTranslation();
  const hasAnyCredentials = Boolean(
    legacyAuthConsumers.length || authClients.length
  );
  const credentials = useMemo(
    () => ({
      legacyTestConsumer: legacyAuthConsumers.find(
        (consumer) => consumer.environment === "test"
      ),
      legacyProdConsumer: legacyAuthConsumers.find(
        (consumer) => consumer.environment === "prod"
      ),
      testClient: authClients.find((client) => client.tenant === "test"),
      prodClient: authClients.find((client) => client.tenant === "prod"),
    }),
    [legacyAuthConsumers, authClients]
  );

  if (!hasAnyCredentials) {
    return (
      <Alert variant={"info"}>{t("integrations.pending_credentials")}</Alert>
    );
  }

  if (type === IntegrationType.Widgets) {
    return (
      <CredentialsWidgets
        {...credentials}
        email={email}
        status={status}
        id={id}
        type={type}
        subscription={subscription}
      />
    );
  }

  return (
    <>
      {credentials.legacyTestConsumer && (
        <CredentialsLegacyAuthConsumers
          {...credentials}
          email={email}
          status={status}
          id={id}
          type={type}
          subscription={subscription}
          keyVisibility={keyVisibility}
        />
      )}
      <CredentialsAuthClients
        {...credentials}
        email={email}
        status={status}
        id={id}
        type={type}
        subscription={subscription}
        keyVisibility={keyVisibility}
      />
    </>
  );
};
