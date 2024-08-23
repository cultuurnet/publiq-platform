import { expect, test } from "@playwright/test";
import { requestActivationAsIntegrator } from "../integrator/request-activation.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { assertKeyVisibility } from "./assert-key-visibility.js";
import { IntegrationStatus } from "@app-types/IntegrationStatus";
import { createIntegration } from "./create-integration.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can approve an integration", async ({ page }) => {
  // create integration as integrator
  const { id: integrationId } = await createIntegration(
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
  await page.locator("#nova-ui-dropdown-button-5").click();
  await page.getByRole("button", { name: "Approve Integration" }).click();
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(page.getByText("active", { exact: true })).toBeVisible();

  await assertKeyVisibility(page, IntegrationStatus.Active);
});
