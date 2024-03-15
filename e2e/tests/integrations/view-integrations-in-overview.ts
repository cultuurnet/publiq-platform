import { expect, test } from "@playwright/test";

test("view my integrations in overview", async ({ page }) => {
  await page.goto("/nl/integraties");
  await expect(page.getByText("Integraties")).toBeVisible();
  await expect(page.getByText("Geen integraties gevonden")).toBeVisible();
});
