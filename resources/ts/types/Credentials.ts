import type { Auth0Tenant } from "./Auth0Tenant";
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
  id: string;
  integrationId: string;
  tenant: Auth0Tenant;
};
export type Credentials = {
  auth0: AuthClient[];
  uitidV1: LegacyAuthConsumer[];
};
