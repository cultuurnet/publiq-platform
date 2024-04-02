import type { Page } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";

export async function createOrganization(page: Page) {
  await page.goto("/admin");
  await page.getByRole("link", { name: "Organizations" }).click();
  await page.getByRole("link", { name: "Create Organization" }).click();
  const organizationName = faker.company.name();
  await page.getByPlaceholder("Name").fill(organizationName);
  await page.getByPlaceholder("Street").fill(faker.location.streetAddress());
  await page.getByPlaceholder("City").fill(faker.location.city());
  await page.getByPlaceholder("Zip").fill(faker.location.zipCode("####"));
  await page.getByPlaceholder("Country").fill(faker.location.country());
  await page.getByPlaceholder("Invoice Email").fill(faker.internet.email());
  await page.getByPlaceholder("Vat").fill("BE 0475 250 609");
  await page.getByRole("button", { name: "Create Organization" }).click();
  return { organizationName, page };
}
