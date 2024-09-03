import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

const integrationTypeValues = Object.entries(IntegrationType);
test.use({ storageState: "playwright/.auth/user.json" });
integrationTypeValues.forEach(([integrationName, integrationType]) => {
  test(`As an integrator I can create a new ${integrationName} integration`, async ({
    page,
  }) => {
    await createIntegrationAsIntegrator(page, integrationType);
  });
});
