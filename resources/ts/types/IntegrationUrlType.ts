import type { Values } from "./Values";

export const IntegrationUrlType = {
  Login: "login",
  Callback: "callback",
  Logout: "logout",
} as const;

export type IntegrationUrlType = Values<typeof IntegrationUrlType>;
