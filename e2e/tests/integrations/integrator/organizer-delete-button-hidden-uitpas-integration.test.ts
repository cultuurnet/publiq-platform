import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { addOrganizer } from "./add-organizer.js";

test("As an integrator I can not remove an organizer from an UiTPAS integration", async ({
  page,
}) => {
  const { integrationId } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.UiTPAS
  );
  await requestActivationAsIntegrator(
    page,
    integrationId,
    IntegrationType.UiTPAS
  );
  await addOrganizer(page);

  await expect(page.getByTestId("Publiq")).toHaveCount(0);
  await expect(page.getByTestId("publiq vzw - UiTPAS organizer")).toHaveCount(
    0
  );
});
