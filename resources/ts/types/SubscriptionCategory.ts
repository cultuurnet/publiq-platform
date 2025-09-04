import type { Values } from "./Values";

export const SubscriptionCategory = {
  Free: "Free",
  Basic: "Basic",
  Plus: "Plus",
  Custom: "Custom",
  UiTnetwerk: "UiTnetwerk",
} as const;

export type SubscriptionCategory = Values<typeof SubscriptionCategory>;
