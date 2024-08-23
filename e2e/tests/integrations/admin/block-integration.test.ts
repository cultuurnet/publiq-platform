import { expect, test } from "@playwright/test";
import { createIntegration } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can block an integration", async ({ page }) => {
  // create integration
  const { id } = await createIntegration(page, IntegrationType.SearchApi);

  // block integration
  await page.goto(`/admin/resources/integrations/${id}`);
  await page.locator("#nova-ui-dropdown-button-5").click();
  await page.getByRole("button", { name: "Block Integration" }).click();
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(page.getByText("blocked", { exact: true })).toBeVisible();
});
