import { type Page, expect } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { IntegrationType } from "@app-types/IntegrationType";

export async function requestActivationAsIntegrator(
  page: Page,
  integrationId: string,
  integrationType: IntegrationType
) {
  await page.goto(`/nl/integraties/${integrationId}`);
  await page.getByRole("button", { name: "Activatie aanvragen" }).click();
  await page
    .getByLabel("Naam bedrijf, organisatie of")
    .fill(faker.company.name());
  await page
    .getByLabel("Straat en nummer")
    .fill(faker.location.streetAddress());
  await page.getByLabel("Postcode").fill(faker.location.zipCode("####"));
  await page.getByLabel(/^Gemeente/).fill(faker.location.city());

  if (
    integrationType === IntegrationType.SearchApi ||
    integrationType === IntegrationType.Widgets
  ) {
    await page.getByLabel("BTW of ondernemingsnummer").fill("BE 0475 250 609");
    await page.getByLabel("E-mail boekhouding").fill(faker.internet.email());
  }

  if (integrationType === IntegrationType.UiTPAS) {
    await page.locator('input[name="organizers"]').click();
    await page.locator('input[name="organizers"]').fill("Publiq");
    await page.getByRole('listitem').getByText("Publiq", { exact: true }).click();
  }

  await page.getByRole("button", { name: "Bevestigen" }).click();
  await expect(page.getByText("Actief")).toBeVisible();
}
