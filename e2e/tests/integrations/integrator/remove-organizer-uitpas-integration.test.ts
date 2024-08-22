import { test, expect, Page } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { addUiTPASOrganizer } from "./add-organizer-uitpas-integration.test.js";

test("As an integrator I can remove an organizer from an UiTPAS integration", async ({
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
  await addUiTPASOrganizer(page);
  await page.locator("#Publiq").click();
  await page.getByRole("button", { name: "Bevestigen" }).click();
  await expect(
    page.getByRole("heading", { name: "Publiq", exact: true })
  ).toBeHidden();
});
