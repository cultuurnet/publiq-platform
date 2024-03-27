import { Values } from "./Values";

export const Currency = {
  EUR: "EUR",
} as const;

export type Currency = Values<typeof Currency>;
