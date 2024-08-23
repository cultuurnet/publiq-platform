import { expect, test } from "@playwright/test";
import { createCoupon } from "./create-coupon.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can create a coupon)", async ({ page }) => {
  await createCoupon(page);
});
