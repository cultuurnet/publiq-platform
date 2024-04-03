import type { ReactNode } from "react";
import React from "react";
import type { Integration } from "../Pages/Integrations";

export const CouponInfoContext = React.createContext(
  undefined as unknown as Integration["coupon"]
);

export const CouponInfoProvider = ({
  couponInfo,
  children,
}: {
  couponInfo: Integration["coupon"];
  children: ReactNode;
}) => {
  return (
    <CouponInfoContext.Provider value={couponInfo}>
      {children}
    </CouponInfoContext.Provider>
  );
};
