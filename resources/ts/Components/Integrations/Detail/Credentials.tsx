import React, { useMemo } from "react";
import { CredentialsLegacyAuthConsumers } from "./CredentialsLegacyAuthConsumers";
import { CredentialsAuthClients } from "./CredentialsAuthClients";
import { IntegrationType } from "../../../types/IntegrationType";
import { CredentialsWidgets } from "./CredentialsWidgets";
import type {
  AuthClient,
  KeycloakClient,
  LegacyAuthConsumer,
} from "../../../types/Credentials";
import type { Integration } from "../../../types/Integration";
import { Alert } from "../../Alert";
import { useTranslation } from "react-i18next";
import { usePolling } from "../../../hooks/usePolling";
import { KeyVisibility } from "../../../types/KeyVisibility";
import { UiTiDv1Environment } from "../../../types/UiTiDv1Environment";
import { Auth0Tenant } from "../../../types/Auth0Tenant";
import { KeycloakEnvironment } from "../../../types/KeycloakEnvironment";
import { usePageProps } from "../../../hooks/usePageProps";

type Props = Integration & {
  email: string;
  oldCredentialsExpirationDate: string;
};

export type Credentials = {
  testClient?: AuthClient;
  prodClient?: AuthClient;
  legacyTestConsumer?: LegacyAuthConsumer;
  legacyProdConsumer?: LegacyAuthConsumer;
  keycloakTestClient?: KeycloakClient;
  keycloakProdClient?: KeycloakClient;
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
  keycloakClients,
  oldCredentialsExpirationDate,
}: Props) => {
  const { t } = useTranslation();
  const { config } = usePageProps();
  const hasCredentials = useMemo(() => {
    if (keyVisibility === KeyVisibility.v1 && !keyVisibilityUpgrade) {
      return legacyAuthConsumers.length > 0;
    }

    const credentials: unknown[][] = [legacyAuthConsumers, authClients];

    if (config.keycloak) {
      credentials.push(keycloakClients);
    }

    return credentials.every((it) => it.length > 0);
  }, [
    authClients,
    config.keycloak,
    keyVisibility,
    keyVisibilityUpgrade,
    keycloakClients,
    legacyAuthConsumers,
  ]);

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
        (client) => client.tenant === Auth0Tenant.Testing
      ),
      prodClient: authClients.find(
        (client) => client.tenant === Auth0Tenant.Production
      ),
      keycloakTestClient: keycloakClients.find(
        (client) => client.environment === KeycloakEnvironment.Testing
      ),
      keycloakProdClient: keycloakClients.find(
        (client) => client.environment === KeycloakEnvironment.Production
      ),
    }),
    [legacyAuthConsumers, authClients, keycloakClients]
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
          oldCredentialsExpirationDate={oldCredentialsExpirationDate}
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
