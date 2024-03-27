import { Page, expect, test } from "@playwright/test";
import { faker } from "@faker-js/faker";

test.use({ storageState: "playwright/.auth/admin.json" });

export const IntegrationTypes = {
  SEARCH_API: "search-api",
  ENTRY_API: "entry-api",
  WIDGETS: "widgets",
} as const;

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

test("create a new integration as an admin (with functional, technical and contributor contact)", async ({
  page,
}) => {

  const integrationPage = await createIntegration(IntegrationTypes.SEARCH_API, page);
  const url = integrationPage.url();
  const integrationId = url.split("/").pop();

  // Add contributor contact
  const contributrorEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole("link", { name: "Create Contact" }).click();
  await page.getByPlaceholder("Email").fill(contributrorEmail);
  await page.locator("#type").selectOption("contributor");
  await page.getByPlaceholder("First Name").fill(faker.person.firstName());
  await page.getByPlaceholder("Last Name").fill(faker.person.lastName());
  await page.getByTestId("integrations").selectOption(integrationId!);
  await page.getByRole("button", { name: "Create Contact" }).click();

  await page.waitForURL(
    /https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/
  );
  await expect(
    page.getByRole("heading", { name: `Contact Details: ${contributrorEmail}` })
  ).toBeVisible();
  await expect(page.getByText("contributor")).toBeVisible();

  // Add functional contact
  const functionalEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole("link", { name: "Create Contact" }).click();
  await page.getByPlaceholder("Email").fill(functionalEmail!);
  await page.locator("#type").selectOption("functional");
  await page.getByPlaceholder("First Name").fill(faker.person.firstName());
  await page.getByPlaceholder("Last Name").fill(faker.person.lastName());
  await page.getByTestId("integrations").selectOption(integrationId!);
  await page.getByRole("button", { name: "Create Contact" }).click();

  await page.waitForURL(
    /https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/
  );
  await expect(
    page.getByRole("heading", { name: `Contact Details: ${functionalEmail}` })
  ).toBeVisible();
  await expect(page.getByText("functional")).toBeVisible();

  // Add technical contact
  const technicalEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole("link", { name: "Create Contact" }).click();
  await page.getByPlaceholder("Email").fill(technicalEmail!);
  await page.locator("#type").selectOption("technical");
  await page.getByPlaceholder("First Name").fill(faker.person.firstName());
  await page.getByPlaceholder("Last Name").fill(faker.person.lastName());
  await page.getByTestId("integrations").selectOption(integrationId!);
  await page.getByRole("button", { name: "Create Contact" }).click();

  await page.waitForURL(
    /https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/
  );
  await expect(
    page.getByRole("heading", { name: `Contact Details: ${technicalEmail}` })
  ).toBeVisible();
  await expect(page.getByText("technical")).toBeVisible();

  // Go to overview page and see if the contacts are visible
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await expect(page.getByText(functionalEmail)).toBeVisible();
  await expect(page.getByText(contributrorEmail)).toBeVisible();
  await expect(page.getByText(technicalEmail)).toBeVisible();
});
