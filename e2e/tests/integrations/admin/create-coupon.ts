import { expect, type Page } from "@playwright/test";
import { faker } from "@faker-js/faker";

export async function createCoupon(page: Page) {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Coupons" }).click();
  await page.getByRole("link", { name: "Create Coupon" }).click();

  const couponCode = faker.string.uuid();

  await page.getByLabel("Coupon code").fill(couponCode);
  await page.getByRole("button", { name: "Create Coupon" }).click();
  await page.waitForURL(/\/admin\/resources\/coupons\/(?!new).+$/);

  await expect(
    page.getByRole("heading", { name: `Coupon Details: ${couponCode}` })
  ).toBeVisible({ timeout: 10_000 });

  return { couponCode };
}
