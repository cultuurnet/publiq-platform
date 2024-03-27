import { IntegrationType } from "./IntegrationType";
import { Currency } from "./Currency";
import { SubscriptionCategory } from "./SubscriptionCategory";

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
