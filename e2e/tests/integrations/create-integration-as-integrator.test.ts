import { expect, test } from "@playwright/test";

test("As an integrator I can create a new integration", async ({ page }) => {
  await page.goto("/nl/integraties");
  await expect(page.locator("h2").getByText("Mijn integraties")).toBeVisible();

  await page.getByRole('main').getByRole('link', { name: 'Integratie toevoegen' }).click();
  await page.getByLabel('Naam integratie').click();
  await page.getByLabel('Naam integratie').fill('Test E2E integration');
  await page.getByLabel('Doel van de integratie').click();
  await page.getByLabel('Doel van de integratie').fill('Test E2e integration');
  await page.locator('input[name="lastNameFunctionalContact"]').click();
  await page.locator('input[name="lastNameFunctionalContact"]').fill('E2E');
  await page.locator('input[name="lastNameFunctionalContact"]').press('Tab');
  await page.locator('input[name="firstNameFunctionalContact"]').fill('test');
  await page.locator('input[name="firstNameFunctionalContact"]').press('Tab');
  await page.locator('input[name="emailFunctionalContact"]').click();
  await page.locator('input[name="emailFunctionalContact"]').fill('dev+e2etest@publiq.be');
  await page.locator('input[name="emailPartner"]').click();
  await page.locator('input[name="emailPartner"]').fill('dev+e2etest@publiq.be');
  await page.locator('#app label').filter({ hasText: 'Ik ga akkoord met' }).click();
  await page.getByLabel('Ik ga akkoord met de').check();
  await page.getByRole('button', { name: 'Integratie aanmaken' }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText('Test E2E integration')).toBeVisible();
});


