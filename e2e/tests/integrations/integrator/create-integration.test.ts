import { test } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";

test.use({ storageState: 'playwright/.auth/user.json' });

test("As an integrator I can create a new integration", async ({ page }) => {
  await createIntegrationAsIntegrator(page);
});


