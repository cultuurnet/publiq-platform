import { Values } from "./Values";

export const IntegrationType = {
  EntryApi: "entry-api",
  SearchApi: "search-api",
  Widgets: "widgets",
} as const;

export type IntegrationType = Values<typeof IntegrationType>;

export const isIntegrationType = (val: unknown): val is IntegrationType => {
  return Object.values(IntegrationType).includes(val as IntegrationType);
};
