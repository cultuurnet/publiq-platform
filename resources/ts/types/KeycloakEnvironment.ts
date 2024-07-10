import type { Values } from "./Values";

export const KeycloakEnvironment = {
  Acceptance: "acc",
  Testing: "test",
  Production: "prod",
} as const;

export type KeycloakEnvironment = Values<typeof KeycloakEnvironment>;
