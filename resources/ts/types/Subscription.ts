import { IntegrationType } from "./IntegrationType";

export type Subscription = {
  id: string;
  name: string;
  description: string;
  category: string;
  integrationType: IntegrationType;
  currency: string;
  price: number;
  fee: number;
};
