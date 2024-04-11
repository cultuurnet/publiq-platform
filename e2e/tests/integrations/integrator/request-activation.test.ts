import { expect, test } from "@playwright/test";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

test("As an integrator I can request activation via organization details", async ({
  page,
}) => {
  // Create SAPI integration
  const { integrationId } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.SearchApi
  );

  // Request activation
  await requestActivationAsIntegrator(
    page,
    integrationId,
    IntegrationType.SearchApi
  );
});
