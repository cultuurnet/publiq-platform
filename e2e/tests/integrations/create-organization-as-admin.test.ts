import { expect, test } from "@playwright/test";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can create a new organization", async ({ page }) => {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Organizations" }).click();
  await page.getByRole("link", { name: "Create Organization" }).click();
  await page.getByPlaceholder("Name").fill("E2E test organization");
  await page.getByPlaceholder("Street").fill("test street 21");
  await page.getByPlaceholder("City").fill("Brussels");
  await page.getByPlaceholder("Zip").fill("1080");
  await page.getByPlaceholder("Country").fill("Belgium");
  await page
    .getByPlaceholder("Invoice Email")
    .fill(process.env.E2E_TEST_ADMIN_EMAIL!);
  await page.getByPlaceholder("Vat").fill("BE 0475 250 609");
  await page.getByRole("button", { name: "Create Organization" }).click();
  await expect(
    page
      .locator("h1")
      .getByText("Organization Details: E2E test organization")
  ).toBeVisible();
});
