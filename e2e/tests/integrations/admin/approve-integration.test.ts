import { expect, test } from "@playwright/test";
import { createIntegrationAsIntegrator } from "../integrator/create-integration.js";
import { requestActivationAsIntegrator } from "../integrator/request-activation.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can activate an integration", async ({ page }) => {

  // create integration
  const integrationPage = await createIntegrationAsIntegrator(page);
  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // request activation
  await requestActivationAsIntegrator(page, integrationId!);

  // activate integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Activate Integration" }).click();
  await page.locator("#organization").selectOption(organizationId!);
  await page.getByRole("button", { name: "Activate" }).click();

  await expect(page.locator(`a[href="/admin/resources/organizations/${organizationId}"]`)).toBeVisible();
  await expect(page.getByText("active", { exact: true })).toBeVisible();
});
