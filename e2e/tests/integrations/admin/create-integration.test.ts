import { Page, expect, test } from "@playwright/test";
import { faker } from "@faker-js/faker";
import { ContactType, ContactTypes, IntegrationTypes, createIntegration } from "./helpers";


async function addContactToIntegration(type: ContactType, integrationId: string, page: Page) {
  const contributrorEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole("link", { name: "Create Contact" }).click();
  await page.getByPlaceholder("Email").fill(contributrorEmail);
  await page.locator("#type").selectOption(type);
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
  await expect(page.getByText(type)).toBeVisible();
  return contributrorEmail;
}

test.use({ storageState: "playwright/.auth/admin.json" });

test("create a new integration as an admin (with functional, technical and contributor contact)", async ({
  page,
}) => {
  const integrationPage = await createIntegration(IntegrationTypes.SEARCH_API, page);
  const url = integrationPage.url();
  const integrationId = url.split("/").pop();

  const contributorEmail = await addContactToIntegration(ContactTypes.CONTRIBUTOR, integrationId!, page);
  const functionalEmail = await addContactToIntegration(ContactTypes.FUNCTIONAL, integrationId!, page);
  const technicalEmail = await addContactToIntegration(ContactTypes.TECHNICAL, integrationId!, page);

  // Go to overview page and see if the contacts are visible
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await expect(page.getByText(functionalEmail)).toBeVisible();
  await expect(page.getByText(contributorEmail)).toBeVisible();
  await expect(page.getByText(technicalEmail)).toBeVisible();
});
