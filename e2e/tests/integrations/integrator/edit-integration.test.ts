import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { fakerNL_BE as faker } from "@faker-js/faker";

test.use({ storageState: "playwright/.auth/user.json" });

test("As an integrator I can edit an existing integration", async ({
  page,
}) => {
  await createIntegrationAsIntegrator(page, IntegrationType.EntryApi);

  // Edit integration
  await page.getByRole("button", { name: "Instellingen" }).click();

  await page.getByLabel("Integratienaam").fill(faker.word.adjective());
  await page.getByLabel("Beschrijving").fill(faker.lorem.lines(2));

  await page.getByRole("button", { name: "Aanpassingen bewaren" }).click();

  await expect(
    page.getByRole("heading", { name: "De wijzigingen zijn succesvol" })
  ).toBeVisible();
});
