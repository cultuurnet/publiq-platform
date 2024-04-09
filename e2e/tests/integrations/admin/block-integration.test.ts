import { expect, test } from "@playwright/test";
import { createIntegration } from "./create-integration.js";
import { IntegrationTypes } from "../../types.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can block an integration", async ({ page }) => {
  // create integration
  const { page: integrationPage, name: integrationName } =
    await createIntegration(page, IntegrationTypes.SEARCH_API);

  await expect(
    page.locator("h1").getByText(`Integration Details: ${integrationName}`)
  ).toBeVisible();

  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // block integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Block Integration" }).click();
  await page.getByRole("button", { name: "Block" }).click();

  await expect(page.getByText("blocked", { exact: true })).toBeVisible();
});
