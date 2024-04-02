import { expect, test } from "@playwright/test";
import { IntegrationTypes, createIntegration } from "./create-integration.ts";
import { createOrganization } from "./create-organization.ts";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can activate an integration", async ({ page }) => {

  // create organization
  const { page: organizationPage} = await createOrganization(page);
  const organizationUrl = organizationPage.url();
  const organizationId = organizationUrl.split("/").pop();

  // create integration
  const {page: integrationPage } = await createIntegration(
    IntegrationTypes.SEARCH_API,
    page
  );
  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // activate integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Activate Integration" }).click();
  await page.locator("#organization").selectOption(organizationId!);
  await page.getByRole("button", { name: "Activate" }).click();

  await expect(page.locator(`a[href="/admin/resources/organizations/${organizationId}"]`)).toBeVisible();
  await expect(page.getByText("active", { exact: true })).toBeVisible();
});
