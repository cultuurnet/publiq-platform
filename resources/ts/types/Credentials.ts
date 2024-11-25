import type { KeycloakEnvironment } from "./KeycloakEnvironment";
import type { UiTiDv1Environment } from "./UiTiDv1Environment";

export type LegacyAuthConsumer = {
  apiKey: string;
  consumerId: string;
  consumerKey: string;
  consumerSecret: string;
  environment: UiTiDv1Environment;
  id: string;
  integrationId: string;
};
export type AuthClient = {
  clientId: string;
  clientSecret: string;
  environment: KeycloakEnvironment;
  id: string;
  integrationId: string;
};

export type Credentials = {
  uitidV1: LegacyAuthConsumer[];
  keycloak: AuthClient[];
};
