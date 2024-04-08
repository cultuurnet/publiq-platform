import { expect, test } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { requestActivationAsIntegrator } from "./request-activation.js";

test("As an integrator I can request activation via organization details", async ({
  page,
}) => {
  // Create SAPI integration
  await page.goto("/nl/integraties");
  await page
    .getByRole("main")
    .getByRole("link", { name: "Integratie toevoegen" })
    .click();

  await page.locator('label[for="search-api"]').click();
  await page.waitForTimeout(500);
  await page
    .locator('label[for="b46745a1-feb5-45fd-8fa9-8e3ef25aac26"]')
    .click();

  const integrationName = faker.word.adjective();
  await page.getByLabel("Naam integratie").fill(integrationName);
  await page.getByLabel("Doel van de integratie").fill(faker.lorem.lines(2));
  await page
    .locator('input[name="lastNameFunctionalContact"]')
    .fill(faker.person.lastName());

  await page.locator('input[name="lastNameFunctionalContact"]').press("Tab");
  await page
    .locator('input[name="firstNameFunctionalContact"]')
    .fill(faker.person.firstName());
  await page.locator('input[name="firstNameFunctionalContact"]').press("Tab");
  await page
    .locator('input[name="emailFunctionalContact"]')
    .fill(faker.internet.email());
  await page
    .locator('input[name="lastNameTechnicalContact"]')
    .fill(faker.person.lastName());
  await page.locator('input[name="lastNameTechnicalContact"]').press("Tab");
  await page
    .locator('input[name="firstNameTechnicalContact"]')
    .fill(faker.person.firstName());
  await page.locator('input[name="firstNameTechnicalContact"]').press("Tab");
  await page.locator('input[name="emailPartner"]').fill(faker.internet.email());
  await page.getByLabel("Ik ga akkoord met de").check();
  await page.getByRole("button", { name: "Integratie aanmaken" }).click();
  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  const url = page.url();
  const newIntegrationId = url.split("/").pop();

  // Request activation
  await requestActivationAsIntegrator(page, newIntegrationId!);
});
