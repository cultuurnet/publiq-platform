import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";

const DUPLICATE_ORGANIZER_ERROR_MESSAGE =
  "De organisatie die je wilt toevoegen is al toegevoegd aan de integratie.";

// The backend only reaches the duplicate_organizer error path (the
// UniqueConstraintViolationException catch in
// IntegrationController::updateOrganizers) on a genuine race between two
// concurrent requests adding the same organizer: the pre-filter and the
// unique() dedup already stop every duplicate a normal, sequential UI
// interaction (or even a double form submission) can produce. That race
// can't be triggered deterministically through the UI, so this test lets
// the real "add organizer" request run against the backend and patches its
// (real, backend-shaped) Inertia response to also carry the
// duplicate_organizer error, to verify the front-end still reads that error
// key and renders it.
test("As an integrator I see an error when adding an organizer fails as a duplicate", async ({
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

  await page.route(
    `**/integrations/${integrationId}/organizers`,
    async (route) => {
      const response = await route.fetch();
      const json = await response.json();
      json.props.errors = {
        ...json.props.errors,
        duplicate_organizer: DUPLICATE_ORGANIZER_ERROR_MESSAGE,
      };
      await route.fulfill({ response, json });
    }
  );

  await page.getByRole("button", { name: "Organisaties" }).click();
  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();
  await page.getByRole("textbox").fill("publiq vzw - UiTPAS organizer");
  await page
    .locator("li")
    .filter({ hasText: "publiq vzw - UiTPAS organizer" })
    .click();
  await page.getByRole("button", { name: "Bevestigen" }).click();

  await expect(page.getByText(DUPLICATE_ORGANIZER_ERROR_MESSAGE)).toBeVisible();
});
