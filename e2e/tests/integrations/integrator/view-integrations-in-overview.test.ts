import { expect, test } from "@playwright/test";

test("view my integrations in overview", async ({ page }) => {
  await page.goto("/nl/integraties");
  await expect(page.locator("h2").getByText("Mijn integraties")).toBeVisible();
});
