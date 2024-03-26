import { expect, test } from "@playwright/test";
import { faker } from '@faker-js/faker';

test.use({ storageState: 'playwright/.auth/user.json' });

test("As an integrator I can create a new integration", async ({ page }) => {
  await page.goto("/nl/integraties");
  await expect(page.locator("h2").getByText("Mijn integraties")).toBeVisible();

  await page.getByRole('main').getByRole('link', { name: 'Integratie toevoegen' }).click();

  const integrationName = faker.word.adjective();
  await page.getByLabel('Naam integratie').fill(integrationName);
  await page.getByLabel('Doel van de integratie').fill(faker.lorem.lines(2));
  await page.locator('input[name="lastNameFunctionalContact"]').fill(faker.person.lastName());
  await page.locator('input[name="lastNameFunctionalContact"]').press('Tab');
  await page.locator('input[name="firstNameFunctionalContact"]').fill(faker.person.firstName());
  await page.locator('input[name="firstNameFunctionalContact"]').press('Tab');
  await page.locator('input[name="emailFunctionalContact"]').fill(faker.internet.email());
  await page.locator('input[name="lastNameTechnicalContact"]').fill(faker.person.lastName());
  await page.locator('input[name="lastNameTechnicalContact"]').press('Tab');
  await page.locator('input[name="firstNameTechnicalContact"]').fill(faker.person.firstName());
  await page.locator('input[name="firstNameTechnicalContact"]').press('Tab');
  await page.locator('input[name="emailPartner"]').fill(faker.internet.email());
  await page.getByLabel('Ik ga akkoord met de').check();
  await page.getByRole('button', { name: 'Integratie aanmaken' }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();
});


