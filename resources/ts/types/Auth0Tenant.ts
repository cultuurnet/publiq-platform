import type { Values } from "./Values";

export const Auth0Tenant = {
  Acceptance: "acc",
  Testing: "test",
  Production: "prod",
} as const;

export type Auth0Tenant = Values<typeof Auth0Tenant>;
