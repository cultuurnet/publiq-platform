import { type Page } from "@playwright/test";
import { faker } from "@faker-js/faker";

export async function createCoupon(page: Page) {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Coupons" }).click();
  await page.getByRole("link", { name: "Create Coupon" }).click();
  await page.waitForLoadState("networkidle");

  const couponCode = faker.string.uuid();

  await page.getByLabel("Coupon code").fill(couponCode);
  await page.getByRole("button", { name: "Create Coupon" }).click();
  await page.waitForLoadState("networkidle");

  return { couponCode, page };
}
