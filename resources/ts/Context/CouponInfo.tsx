import type { ReactNode } from "react";
import React from "react";
import type { CouponInfo } from "../Pages/Integrations/Detail";

export const CouponInfoContext = React.createContext(
  undefined as unknown as CouponInfo
);

export const CouponInfoProvider = ({
  couponInfo,
  children,
}: {
  couponInfo: CouponInfo;
  children: ReactNode;
}) => {
  return (
    <CouponInfoContext.Provider value={couponInfo}>
      {children}
    </CouponInfoContext.Provider>
  );
};
