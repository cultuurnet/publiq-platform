import { Subscription } from "../types/Subscription";

export const getPriceLabelFromSubscription = (subscription: Subscription) => {
  if (subscription.category === "Custom") {
    return "Nog te bepalen";
  }

  const currency =
    subscription.currency === "EUR" ? "€" : subscription.currency;

  const price =
    subscription.fee !== 0
      ? `${subscription.price} yearly (${subscription.fee} one-time)`
      : `${subscription.price} yearly`;

  return `${subscription.category} ${currency} ${price}`;
};
