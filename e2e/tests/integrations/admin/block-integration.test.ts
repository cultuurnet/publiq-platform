import { expect, test } from "@playwright/test";
import { IntegrationTypes, createIntegration } from "./helpers.ts";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can block an integration", async ({ page }) => {

  // create integration
  const integrationPage = await createIntegration(
    IntegrationTypes.SEARCH_API,
    page
  );
  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // block integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Block Integration" }).click();
  await page.getByRole("button", { name: "Block" }).click();

  await expect(page.getByText("blocked", { exact: true })).toBeVisible();
});
