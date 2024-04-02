import type { Currency } from "../types/Currency";

export const formatCurrency = (currency: Currency, amount: number) =>
  Intl.NumberFormat("nl-BE", {
    currency: currency,
    style: "currency",
    maximumFractionDigits: 0,
  }).format(amount);
