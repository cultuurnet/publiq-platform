import { expect, type Page } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { IntegrationType } from "@app-types/IntegrationType";

export async function createIntegrationAsIntegrator(
  page: Page,
  integrationType: IntegrationType,
  couponCode?: string
) {
  await page.goto("/nl/integraties");
  await page
    .getByRole("main")
    .getByRole("link", { name: "Integratie toevoegen" })
    .click();

  if (integrationType === IntegrationType.EntryApi) {
    await page
      .locator("li")
      .filter({ hasText: /^Entry API/ })
      .click();
  }

  if (integrationType === IntegrationType.SearchApi) {
    await page
      .locator("li")
      .filter({ hasText: /^Search API/ })
      .click();
  }

  if (integrationType === IntegrationType.Widgets) {
    await page
      .locator("li")
      .filter({ hasText: /^Widgets/ })
      .click();
  }

  if (integrationType === IntegrationType.UiTPAS) {
    await page
      .locator("li")
      .filter({ hasText: /^UiTPAS API/ })
      .click();
  }

  if (
    integrationType === IntegrationType.SearchApi ||
    integrationType === IntegrationType.Widgets
  ) {
    await page.getByLabel("Basic (€ 125 / jaar)Je zorgt").check();
  }

  const integrationName = faker.word.adjective();
  await page.getByLabel("Naam integratie").fill(integrationName);
  await page.getByLabel("Doel van de integratie").fill(faker.lorem.lines(2));
  await page.locator('input[name="website"]').fill(faker.internet.url());
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

  await page.locator('input[name="agreement"]').check();

  if (integrationType === IntegrationType.UiTPAS) {
    await page.locator('input[name="uitpasAgreement"]').check();
  }

  if (
    couponCode &&
    (integrationType === IntegrationType.SearchApi ||
      integrationType === IntegrationType.Widgets)
  ) {
    await page.getByLabel("Ik heb een coupon").check();
    await page.locator('input[name="coupon"]').fill(couponCode);
  }

  await page.getByRole("button", { name: "Integratie aanmaken" }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/nl\/integraties(\/.*)?/);
  await expect(page.getByText(integrationName)).toBeVisible();

  const integrationId = page.url().split("/").pop()!;

  return { integrationName, integrationId };
}
