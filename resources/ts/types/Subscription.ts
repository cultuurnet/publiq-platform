import type { IntegrationType } from "./IntegrationType";
import type { Currency } from "./Currency";
import type { SubscriptionCategory } from "./SubscriptionCategory";

export type Subscription = {
  id: string;
  name: string;
  description: string;
  category: SubscriptionCategory;
  integrationType: IntegrationType;
  currency: Currency;
  price: number;
  fee: number;
};
