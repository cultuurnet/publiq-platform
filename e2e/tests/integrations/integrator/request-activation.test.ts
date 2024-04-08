import { expect, test } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationTypes } from "../../types.js";

test("As an integrator I can request activation via organization details", async ({
  page,
}) => {
  // Create SAPI integration
  const { integrationName} = await createIntegrationAsIntegrator(page, IntegrationTypes.SEARCH_API);

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  const url = page.url();
  const newIntegrationId = url.split("/").pop();

  // Request activation
  await requestActivationAsIntegrator(page, newIntegrationId!, IntegrationTypes.SEARCH_API);
});
