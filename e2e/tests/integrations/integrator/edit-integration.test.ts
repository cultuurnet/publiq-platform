import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationTypes } from "../../types.js";
import { fakerNL_BE as faker } from "@faker-js/faker";

test.use({ storageState: "playwright/.auth/user.json" });

test("As an integrator I can edit an existing integration", async ({
  page,
}) => {
  const { integrationName } = await createIntegrationAsIntegrator(
    page,
    IntegrationTypes.SEARCH_API
  );

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  // Edit integration
  await page.getByRole("button", { name: "Instellingen" }).click();

  await page.getByLabel("Integratienaam").fill(faker.word.adjective());
  await page.getByLabel("Beschrijving").fill(faker.lorem.lines(2));


  await page
    .locator("div")
    .filter({ hasText: /^Login URLtestomgevingproductieomgeving$/ })
    .getByRole("textbox")
    .first()
    .fill(faker.internet.url());

  await page
    .locator("div")
    .filter({ hasText: /^Login URLtestomgevingproductieomgeving$/ })
    .getByRole("textbox")
    .first()
    .fill(faker.internet.url());

  await page.getByRole("button", { name: "Aanpassingen bewaren" }).click();

  await expect(page.getByRole("heading", { name: "De wijzigingen zijn succesvol" })).toBeVisible();
  await page.screenshot({ path: "edit-integration.png" });
});
