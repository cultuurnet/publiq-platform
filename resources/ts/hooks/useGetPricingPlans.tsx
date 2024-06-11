import type { TFunction } from "i18next";
import type { IntegrationType } from "../types/IntegrationType";
import { SubscriptionCategory } from "../types/SubscriptionCategory";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";
import type { Subscription } from "../types/Subscription";
import { formatCurrency } from "../utils/formatCurrency";

export type PricingPlan = {
  id: string;
  title: string;
  label: string;
  price: string;
  description: string;
};

const getPricingPlans = (
  t: TFunction,
  integrationType: IntegrationType,
  subscriptions: Subscription[]
) => {
  const getInfoForCategory = (
    category: SubscriptionCategory
  ): PricingPlan | undefined => {
    const data = subscriptions.find(
      (sub) =>
        sub.category === category && sub.integrationType === integrationType
    );

    if (!data) {
      return undefined;
    }

    const categoryLowercase = category.toLowerCase();

    const title = t(`pricing_plan.${categoryLowercase}.title`);
    const price = t(`pricing_plan.${categoryLowercase}.price`, {
      price: formatCurrency(data.currency, data.price),
      fee: formatCurrency(data.currency, data.fee),
    });

    return {
      id: data.id,
      title: title,
      label: data.category === "Free" ? title : `${title} (${price})`,
      description: t(
        `pricing_plan.${categoryLowercase}.description.${integrationType}`,
        data.description
      ),
      price,
    };
  };

  return Object.values(SubscriptionCategory)
    .map(getInfoForCategory)
    .filter((info): info is PricingPlan => !!info?.id);
};

export const useGetPricingPlans = (
  integrationType: IntegrationType,
  subscriptions: Subscription[]
) => {
  const { t } = useTranslation();

  return useMemo(
    () => getPricingPlans(t, integrationType, subscriptions),
    [t, integrationType, subscriptions]
  );
};
