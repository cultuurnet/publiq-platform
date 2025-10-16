import { expect, test } from "@playwright/test";
import { createIntegrationAsIntegrator } from "../integrator/create-integration.js";
import { requestActivationAsIntegrator } from "../integrator/request-activation.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { assertKeyVisibility } from "./assert-key-visibility.js";
import { IntegrationStatus } from "@app-types/IntegrationStatus";

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

  await page.waitForTimeout(10000);

  // approve integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.locator(`[dusk="${integrationId}-control-selector"]`).click();
  await page.getByRole("button", { name: "Approve Integration" }).click();
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(page.getByText('Statusactive', { exact: true })).toBeVisible();

  await assertKeyVisibility(page, IntegrationStatus.Active);
});
