import type { Values } from "./Values";
import type { IntegrationType } from "./IntegrationType";
import type { IntegrationStatus } from "./IntegrationStatus";
import type { Contact } from "./Contact";
import type { Organization } from "./Organization";
import type { Subscription } from "./Subscription";
import type { IntegrationUrl } from "./IntegrationUrl";
import type { AuthClient, LegacyAuthConsumer } from "./Credentials";
import type { KeyVisibility } from "./KeyVisibility";

export type Coupon = {
  code: string;
  id: string;
  integrationId: string;
  isDistributed: boolean;
  reduction: number;
};

export type Integration = {
  id: string;
  type: Values<typeof IntegrationType>;
  name: string;
  description: string;
  subscriptionId: string;
  coupon: Coupon;
  status: IntegrationStatus;
  contacts: Contact[];
  organization?: Organization;
  subscription: Subscription;
  urls: IntegrationUrl[];
  authClients: AuthClient[];
  legacyAuthConsumers: LegacyAuthConsumer[];
  keyVisibility: KeyVisibility;
};