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
import { usePolling } from "../../../hooks/usePolling";
import { KeyVisibility } from "../../../types/KeyVisibility";
import { UiTiDv1Environment } from "../../../types/UiTiDv1Environment";
import { KeycloakEnvironment } from "../../../types/KeycloakEnvironment";

type Props = Integration & {
  email: string;
  keyVisibleUntil: string;
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
  keyVisibilityUpgrade,
  legacyAuthConsumers,
  authClients,
  keyVisibleUntil,
}: Props) => {
  const { t } = useTranslation();
  const hasCredentials =
    keyVisibility !== KeyVisibility.v2
      ? legacyAuthConsumers.length > 0
      : authClients.length > 0;

  const isV1Upgraded =
    keyVisibility === KeyVisibility.v1 && !!keyVisibilityUpgrade;
  usePolling(!hasCredentials || isV1Upgraded, { only: ["integration"] });
  const credentials = useMemo(
    () => ({
      legacyTestConsumer: legacyAuthConsumers.find(
        (consumer) => consumer.environment === UiTiDv1Environment.Testing
      ),
      legacyProdConsumer: legacyAuthConsumers.find(
        (consumer) => consumer.environment === UiTiDv1Environment.Production
      ),
      testClient: authClients.find(
        (client) => client.environment === KeycloakEnvironment.Testing
      ),
      prodClient: authClients.find(
        (client) => client.environment === KeycloakEnvironment.Production
      ),
    }),
    [legacyAuthConsumers, authClients]
  );

  if (!hasCredentials) {
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
      {keyVisibility !== KeyVisibility.v2 && (
        <CredentialsLegacyAuthConsumers
          {...credentials}
          email={email}
          status={status}
          id={id}
          type={type}
          subscription={subscription}
          keyVisibility={keyVisibility}
          keyVisibleUntil={keyVisibleUntil}
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
        keyVisibilityUpgrade={keyVisibilityUpgrade}
      />
    </>
  );
};
