import type { Values } from "./Values";

export const IntegrationType = {
  EntryApi: "entry-api",
  SearchApi: "search-api",
  Widgets: "widgets",
  UiTPAS: "uitpas-api",
} as const;

export type IntegrationType = Values<typeof IntegrationType>;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const isIntegrationType = (val: any): val is IntegrationType => {
  return Object.values(IntegrationType).includes(val);
};
