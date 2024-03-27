import { Currency } from "../types/Currency";
import { TFunction } from "i18next";
import { IntegrationType } from "../types/IntegrationType";
import { SubscriptionCategory } from "../types/SubscriptionCategory";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { Subscription } from "../types/Subscription";
import { formatCurrency } from "../utils/formatCurrency";

export type PricingPlan = {
  id: string;
  title: string;
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

    return {
      id: data.id,
      title: t(`pricing_plan.${categoryLowercase}.title`),
      description: t(
        `pricing_plan.${categoryLowercase}.description.${integrationType}`,
        data.description
      ),
      price: t(`pricing_plan.${categoryLowercase}.price`, {
        price: formatCurrency(data.currency, data.price),
        fee: formatCurrency(data.currency, data.fee),
      }),
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
