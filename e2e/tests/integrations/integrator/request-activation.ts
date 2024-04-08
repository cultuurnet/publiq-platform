import { type Page, expect } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { IntegrationTypes, type IntegrationType } from "../../types.js";

export async function requestActivationAsIntegrator(
  page: Page,
  integrationId: string,
  integrationType: IntegrationType
) {
  await page.goto(`/nl/integraties/${integrationId}`);
  await page
    .getByRole("button", { name: "Activatie aanvragen" })
    .nth(1)
    .click();
  await page
    .getByLabel("Naam bedrijf, organisatie of")
    .nth(1)
    .fill(faker.company.name());
  await page
    .getByLabel("Straat en nummer")
    .nth(1)
    .fill(faker.location.streetAddress());
  await page.getByLabel("Postcode").nth(1).fill(faker.location.zipCode("####"));
  await page.getByLabel("Gemeente").nth(3).fill(faker.location.city());

  if (integrationType !== IntegrationTypes.ENTRY_API) {
    await page
      .getByLabel("BTW of ondernemingsnummer")
      .nth(1)
      .fill("BE 0475 250 609");
    await page
      .getByLabel("E-mail boekhouding")
      .nth(1)
      .fill(faker.internet.email());
  }

  await page.getByRole("button", { name: "Bevestigen" }).nth(1).click();
  await expect(page.getByText("Actief").nth(1)).toBeVisible();
}
