import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { addOrganizer } from "./add-organizer.js";

const organizerName = "publiq vzw - UiTPAS organizer";

test("As an integrator I cannot add an organizer that is already active on the integration", async ({
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
  await addOrganizer(page, organizerName);

  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();
  await page.getByRole("textbox").fill(organizerName);

  const alreadyActiveOption = page
    .locator("li")
    .filter({ hasText: organizerName });
  await expect(alreadyActiveOption).toContainText("reeds toegevoegd");

  await alreadyActiveOption.click();

  // Clicking the already-active organizer must not select it: no chip is
  // added to the form and the confirm action stays a no-op for it.
  await expect(
    page.locator("li").filter({ hasText: organizerName })
  ).toHaveCount(1);
  await expect(
    page.locator("div.border.rounded").filter({ hasText: organizerName })
  ).toHaveCount(0);

  await page.getByRole("button", { name: "Annuleren" }).click();

  // The organizer is still listed exactly once as an existing organizer.
  await expect(page.getByRole("heading", { name: organizerName })).toHaveCount(
    1
  );
});
