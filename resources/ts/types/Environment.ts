import type { Values } from "./Values";

export const Environment = {
  Test: "test",
  Prod: "prod",
} as const;

export type Environment = Values<typeof Environment>;
