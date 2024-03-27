import { Subscription } from "../types/Subscription";
import { Currency } from "../types/Currency";
import { SubscriptionCategory } from "../types/SubscriptionCategory";

const currencyToSymbol = {
  [Currency.EUR]: "€",
};

export const getPriceLabelFromSubscription = (subscription: Subscription) => {
  if (subscription.category === SubscriptionCategory.Custom) {
    return "Nog te bepalen";
  }

  const currencySymbol = currencyToSymbol[subscription.currency];

  const price =
    subscription.fee !== 0
      ? `${subscription.price} yearly (${subscription.fee} one-time)`
      : `${subscription.price} yearly`;

  return `${subscription.category} ${currencySymbol} ${price}`;
};
