import { Values } from "./Values";

export const Environment = {
  Acc: "acc",
  Test: "test",
  Prod: "prod",
} as const;

export type Environment = Values<typeof Environment>;
