import { expect, test } from "@playwright/test";
import { createIntegrationAsIntegrator } from "../integrator/create-integration.js";
import { requestActivationAsIntegrator } from "../integrator/request-activation.js";
import { IntegrationTypes } from "../../types.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can approve an integration", async ({ page }) => {
  // create integration as integrator
  const {page: integrationPage, integrationName} = await createIntegrationAsIntegrator(
    page,
    IntegrationTypes.ENTRY_API
  );

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  const integrationUrl = integrationPage.url();
  const integrationId = integrationUrl.split("/").pop();

  // request activation
  await requestActivationAsIntegrator(
    page,
    integrationId!,
    IntegrationTypes.ENTRY_API
  );

  // approve integration
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await page.getByRole("button", { name: "Actions" }).click();
  await page.getByRole("button", { name: "Approve Integration" }).click();
  await page.getByRole("button", { name: "Approve" }).click();

  await expect(page.getByText("active", { exact: true })).toBeVisible();
});
