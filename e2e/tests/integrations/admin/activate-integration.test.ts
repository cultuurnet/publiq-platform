import { expect, test } from "@playwright/test";
import { createIntegration } from "./create-integration.js";
import { createOrganization } from "./create-organization.js";
import { IntegrationType } from "@app-types/IntegrationType";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can activate an integration", async ({ page }) => {
  // create organization
  const { page: organizationPage, organizationName } =
    await createOrganization(page);
  await expect(
    page.locator("h1").getByText(`Organization Details: ${organizationName}`)
  ).toBeVisible();
  const organizationUrl = organizationPage.url();
  const organizationId = organizationUrl.split("/").pop();

  // create integration
  const { page: integrationPage, name: integrationName } =
    await createIntegration(page, IntegrationType.SearchApi);

  await expect(
    page.locator("h1").getByText(`Integration Details: ${integrationName}`)
  ).toBeVisible();

  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // activate integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.locator("#nova-ui-dropdown-button-5").click();
  await page.getByRole("button", { name: "Activate Integration" }).click();
  await page.locator("#organization").selectOption(organizationId!);
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(
    page.locator(`a[href="/admin/resources/organizations/${organizationId}"]`)
  ).toBeVisible();
  await expect(page.getByText("active", { exact: true })).toBeVisible();
});
