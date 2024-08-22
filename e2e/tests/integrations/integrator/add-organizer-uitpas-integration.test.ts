import { test, expect, Page } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";

export async function addUiTPASOrganizer(
  page: Page,
  organizerName = "publiq vzw - UiTPAS organizer"
) {
  await page.getByRole("button", { name: "Organisaties" }).click();
  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();
  await page.getByRole("textbox").click();
  await page.getByRole("textbox").fill(organizerName);
  await page.locator("li").filter({ hasText: organizerName }).click();
  await page.getByRole("button", { name: "Bevestigen" }).click();
}

test("As an integrator I can add an organizer to an UiTPAS integration", async ({
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
  await expect(
    page.getByRole("heading", { name: "publiq vzw - UiTPAS organizer" })
  ).toBeVisible();
});
