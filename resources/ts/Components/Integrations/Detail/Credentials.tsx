import React, { useMemo } from "react";
import type { Integration } from "../../../Pages/Integrations/Index";
import { CredentialsLegacyAuthConsumers } from "./CredentialsLegacyAuthConsumers";
import { CredentialsAuthClients } from "./CredentialsAuthClients";
import { IntegrationType } from "../../../types/IntegrationType";
import { CredentialsWidgets } from "./CredentialsWidgets";
import type { AuthClient, LegacyAuthConsumer } from "../../../types/Credentials";

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
  legacyAuthConsumers,
  authClients,
}: Props) => {
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
      <CredentialsLegacyAuthConsumers
        {...credentials}
        email={email}
        status={status}
        id={id}
        type={type}
        subscription={subscription}
      />
      <CredentialsAuthClients
        {...credentials}
        email={email}
        status={status}
        id={id}
        type={type}
        subscription={subscription}
      />
    </>
  );
};
