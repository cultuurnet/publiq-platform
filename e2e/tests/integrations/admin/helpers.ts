import { Page, expect } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";

export const ContactTypes = {
    FUNCTIONAL: "functional",
    TECHNICAL: "technical",
    CONTRIBUTOR: "contributor",
  } as const;
  
  export const IntegrationTypes = {
    SEARCH_API: "search-api",
    ENTRY_API: "entry-api",
    WIDGETS: "widgets",
  } as const;
  
  export type ContactType = typeof ContactTypes[keyof typeof ContactTypes];
  type IntegrationType = typeof IntegrationTypes[keyof typeof IntegrationTypes];
  
  const IntegrationTypeSubscriptionMap = {
    'search-api': 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26',
    'widgets': 'c470ccbf-074c-4bf1-b526-47c94c5e9296',
    'entry-api': '6311ba66-91c2-4905-a182-150f1cdf4825',
  } as const;
  
  export async function createIntegration(type: IntegrationType, page: Page) {
    // Make the integration
    const name = faker.word.adjective();
    await page.goto("/admin");
    await page.getByRole("link", { name: "Integrations" }).click();
    await page.getByRole("link", { name: "Create Integration" }).click();
    await page.getByPlaceholder("Name").fill(name);
    await page.locator("#type").selectOption(type);
    await page.locator("#key_visibility").selectOption("all");
    await page.getByPlaceholder("Description").fill(faker.lorem.lines(2));
    await page
      .getByTestId("subscriptions")
      .selectOption(IntegrationTypeSubscriptionMap[type]);
    await page.getByRole("button", { name: "Create Integration" }).click();
    await expect(
      page.locator("h1").getByText(`Integration Details: ${name}`)
    ).toBeVisible();
    return page;
  }

  export async function createOrganization(page: Page) {
    await page.goto("/admin");
    await page.getByRole("link", { name: "Organizations" }).click();
    await page.getByRole("link", { name: "Create Organization" }).click();
    const organizationName = faker.company.name()
    await page.getByPlaceholder("Name").fill(organizationName);
    await page.getByPlaceholder("Street").fill(faker.location.streetAddress());
    await page.getByPlaceholder("City").fill(faker.location.city());
    await page.getByPlaceholder("Zip").fill(faker.location.zipCode('####'));
    await page.getByPlaceholder("Country").fill(faker.location.country());
    await page
      .getByPlaceholder("Invoice Email")
      .fill(faker.internet.email());
    await page.getByPlaceholder("Vat").fill("BE 0475 250 609");
    await page.getByRole("button", { name: "Create Organization" }).click();
    await expect(
      page
        .locator("h1")
        .getByText(`Organization Details: ${organizationName}`)
    ).toBeVisible();
    return page;
  }
  
  