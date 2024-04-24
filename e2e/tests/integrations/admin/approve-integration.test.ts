import { expect, test } from "@playwright/test";
import { createIntegrationAsIntegrator } from "../integrator/create-integration.js";
import { requestActivationAsIntegrator } from "../integrator/request-activation.js";
import { IntegrationType } from "@app-types/IntegrationType";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can approve an integration", async ({ page }) => {
  // create integration as integrator
  const { integrationId } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.EntryApi
  );

  // request activation
  await requestActivationAsIntegrator(
    page,
    integrationId,
    IntegrationType.EntryApi
  );

  await page.waitForTimeout(1000);
  
  // approve integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Approve Integration" }).click();
  await page.getByRole("button", { name: "Approve" }).click();

  await expect(page.getByText("active", { exact: true })).toBeVisible();
});
