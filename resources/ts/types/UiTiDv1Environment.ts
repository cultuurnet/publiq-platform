import type { Values } from "./Values";

export const UiTiDv1Environment = {
  Acceptance: "acc",
  Testing: "test",
  Production: "prod",
} as const;

export type UiTiDv1Environment = Values<typeof UiTiDv1Environment>;
