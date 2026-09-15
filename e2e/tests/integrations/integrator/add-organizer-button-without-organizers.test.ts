import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";

test("As an integrator I can still see the add organizer button on an UiTPAS integration without any organizers", async ({
  page,
}) => {
  await createIntegrationAsIntegrator(page, IntegrationType.UiTPAS);

  await page.getByRole("button", { name: "Organisaties" }).click();

  await expect(
    page.getByRole("button", { name: "Organisatie toevoegen" })
  ).toBeVisible();
});
