import { expect, Page } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";

export async function createOrganization(page: Page) {
  await page.goto("/admin/resources/integrations");
  await page.getByRole("link", { name: "Organizations" }).waitFor({ state: "visible" });
  await page.getByRole("link", { name: "Organizations" }).click();
  await page.getByRole("link", { name: "Create Organization" }).click();
  const name = faker.company.name();
  await page.getByPlaceholder("Name").fill(name);
  await page.getByPlaceholder("Street").fill(faker.location.streetAddress());
  await page.getByPlaceholder("City").fill(faker.location.city());
  await page.getByPlaceholder("Zip").fill(faker.location.zipCode("####"));
  await page.getByPlaceholder("Country").fill(faker.location.country());
  await page.getByPlaceholder("Invoice Email").fill(faker.internet.email());
  await page.getByPlaceholder("Vat").fill("BE 0475 250 609");
  await page.getByRole("button", { name: "Create Organization" }).click();

  await expect(
    page.locator("h1").getByText(`Organization Details: ${name}`)
  ).toBeVisible();
  const organizationUrl = page.url();
  const id = organizationUrl.split("/").pop()!;

  return { id, name };
}
