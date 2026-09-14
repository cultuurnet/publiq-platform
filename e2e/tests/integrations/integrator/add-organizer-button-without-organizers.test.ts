import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";

test("As an integrator I can still see the add organizer button after removing the last organizer from an UiTPAS integration", async ({
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

  await page.getByRole("button", { name: "Organisaties" }).click();
  await expect(
    page.getByRole("button", { name: "Organisatie toevoegen" })
  ).toBeVisible();

  await page.getByTestId("Publiq").click();
  await page.getByRole("button", { name: "Bevestigen" }).click();
  await expect(
    page.getByRole("heading", { name: "Publiq", exact: true })
  ).toBeHidden();

  await expect(
    page.getByRole("button", { name: "Organisatie toevoegen" })
  ).toBeVisible();
});
