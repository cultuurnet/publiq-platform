import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationTypes } from "../../types.js";

test.use({ storageState: 'playwright/.auth/user.json' });

test("As an integrator I can create a new integration", async ({ page }) => {
  const { integrationName} = await createIntegrationAsIntegrator(page, IntegrationTypes.SEARCH_API);

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();
});


