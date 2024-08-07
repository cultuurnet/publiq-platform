import type { Environment } from "./Environment";
import type { IntegrationUrlType } from "./IntegrationUrlType";

export type IntegrationUrl = {
  id: string;
  environment: Environment;
  type: IntegrationUrlType;
  url: string;
};
