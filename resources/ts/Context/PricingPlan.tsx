import type { ReactNode } from "react";
import React from "react";
import type { PricingPlan } from "../hooks/useGetPricingPlans";

export const PricingPlanContext = React.createContext(
  undefined as unknown as PricingPlan
);

export const PricingPlanProvider = ({
  pricingPlan,
  children,
}: {
  pricingPlan: PricingPlan;
  children: ReactNode;
}) => {
  return (
    <PricingPlanContext.Provider value={pricingPlan}>
      {children}
    </PricingPlanContext.Provider>
  );
};
