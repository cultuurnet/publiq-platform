import type { Values } from "./Values";

export const ContactType = {
  Functional: "functional",
  Technical: "technical",
  Contributor: "contributor",
} as const;

export type ContactType = Values<typeof ContactType>;
