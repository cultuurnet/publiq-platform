import { expect, test } from "@playwright/test";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

test("As an integrator I can request activation via organization details", async ({
  page,
}) => {
  // Create SAPI integration
  const { integrationName } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.SearchApi
  );

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  const url = page.url();
  const newIntegrationId = url.split("/").pop();

  // Request activation
  await requestActivationAsIntegrator(
    page,
    newIntegrationId!,
    IntegrationType.SearchApi
  );
});
