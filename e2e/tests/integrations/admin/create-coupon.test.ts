import { expect, test } from "@playwright/test";
import { createCoupon } from "./create-coupon.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can create a coupon)", async ({ page }) => {
 
  const { couponCode } = await createCoupon(page);

  await expect(
    page.getByText(couponCode, { exact: true })
  ).toBeVisible();

});
