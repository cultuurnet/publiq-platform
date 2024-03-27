import { Environment } from "./Environment";
import { IntegrationUrlType } from "./IntegrationUrlType";

export type IntegrationUrl = {
  id: string;
  environment: Environment;
  type: IntegrationUrlType;
  url: string;
};
