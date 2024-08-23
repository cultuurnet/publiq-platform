import { expect, test } from "@playwright/test";
import { createIntegration } from "./create-integration.js";
import { createOrganization } from "./create-organization.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { assertKeyVisibility } from "./assert-key-visibility.js";
import { IntegrationStatus } from "@app-types/IntegrationStatus";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can activate an integration", async ({ page, context }) => {
  const newPage = await context.newPage();
  const [organization, integration] = await Promise.all([
    createOrganization(page),
    createIntegration(newPage, IntegrationType.SearchApi),
  ]);
  await newPage.close();

  // activate integration
  await page.goto(`/admin/resources/integrations/${integration.id}`);
  await page.locator("#nova-ui-dropdown-button-5").click();
  await page.getByRole("button", { name: "Activate Integration" }).click();
  await page.locator("#organization").selectOption(organization.id);
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(
    page.locator(`a[href="/admin/resources/organizations/${organization.id}"]`)
  ).toBeVisible();
  await expect(page.getByText("active", { exact: true })).toBeVisible();

  await assertKeyVisibility(page, IntegrationStatus.Active);
});
