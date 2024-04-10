import { expect, test } from "@playwright/test";
import { faker } from "@faker-js/faker";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can create a coupon)", async ({ page }) => {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Coupons" }).click();
  await page.getByRole("link", { name: "Create Coupon" }).click();
  await page.waitForLoadState("networkidle");

  const couponCode = faker.string.uuid();

  await page.getByLabel("Coupon code").fill(couponCode);
  await page.getByRole("button", { name: "Create Coupon" }).click();
  await page.waitForLoadState("networkidle");

  await expect(
    page.locator("h1").getByText(`Coupon Details: ${couponCode}`)
  ).toBeVisible();

});
