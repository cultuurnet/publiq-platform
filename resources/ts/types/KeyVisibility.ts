import { Values } from "./Values";

export const KeyVisibility = {
  v1: "v1",
  v2: "v2",
  all: "all",
} as const;

export type KeyVisibility = Values<typeof KeyVisibility>;
