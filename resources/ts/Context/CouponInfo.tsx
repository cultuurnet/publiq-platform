import React, { ReactNode } from "react";
import { CouponInfo } from "../Pages/Integrations/Detail";

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
