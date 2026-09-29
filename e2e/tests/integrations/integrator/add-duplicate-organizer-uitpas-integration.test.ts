import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { addOrganizer } from "./add-organizer.js";

const DUPLICATE_ORGANIZER_ERROR_MESSAGE =
  "De organisatie die je wilt toevoegen is al toegevoegd aan de integratie.";

test("As an integrator I see an error when adding an organizer that is already added", async ({
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
  await addOrganizer(page);

  await expect(page.getByText(DUPLICATE_ORGANIZER_ERROR_MESSAGE)).toBeVisible();
});
