export const formatPricing = (data: { currency: string; price: number }) => {
  return Intl.NumberFormat("nl-BE", {
    currency: data.currency,
    style: "currency",
    maximumFractionDigits: 0,
  }).format(data.price);
};
