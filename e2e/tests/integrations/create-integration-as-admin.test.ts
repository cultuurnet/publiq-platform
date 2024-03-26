import { expect, test } from "@playwright/test";

test.use({ storageState: 'playwright/.auth/admin.json' });

test("create a new integration as an admin", async ({ page }) => {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Integrations" }).click();
  await page.getByRole("link", { name: "Create Integration" }).click();
  await page.getByPlaceholder("Name").fill("Test E2E integration as admin");
  await page.locator("#type").selectOption("entry-api");
  await page.locator("#key_visibility").selectOption("all");
  await page
    .getByPlaceholder("Description")
    .fill("Test E2E integration as admin");
  await page
    .getByTestId("subscriptions")
    .selectOption("b46745a1-feb5-45fd-8fa9-8e3ef25aac26");
  await page.getByRole("button", { name: "Create Integration" }).click();
  await page
    .getByRole("heading", { name: "Integration Details: Test E2E" })
    .click();
  await expect(
    page
      .locator("h1")
      .getByText("Integration Details: Test E2E integration as admin")
  ).toBeVisible();
});
