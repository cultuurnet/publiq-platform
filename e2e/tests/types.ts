export const IntegrationTypes = {
  SEARCH_API: "search-api",
  ENTRY_API: "entry-api",
  WIDGETS: "widgets",
} as const;

export type IntegrationType =
  (typeof IntegrationTypes)[keyof typeof IntegrationTypes];
