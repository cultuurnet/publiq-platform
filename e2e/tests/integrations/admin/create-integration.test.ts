import { expect, test, type Page } from "@playwright/test";
import { faker } from "@faker-js/faker";
import { createIntegration } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { ContactType } from "@app-types/ContactType";

async function addContactToIntegration(
  type: ContactType,
  integrationId: string,
  page: Page
) {
  await page.goto("/admin/resources/contacts/new");
  const email = faker.internet.email();
  await page.getByPlaceholder("Email").fill(email);
  await page.locator("#type").selectOption(type);
  await page.getByPlaceholder("First Name").fill(faker.person.firstName());
  await page.getByPlaceholder("Last Name").fill(faker.person.lastName());
  await page
    .locator("[dusk='integrations-select']")
    .selectOption(integrationId);
  await page.getByRole("button", { name: "Create Contact" }).click();
  await expect(page.getByText("Contact was created!")).toBeVisible({
    timeout: 10_000,
  });

  return { email };
}

test.use({ storageState: "playwright/.auth/admin.json" });

test("create a new integration as an admin (with functional, technical and contributor contact)", async ({
  page,
  context,
}) => {
  const { id } = await createIntegration(page, IntegrationType.SearchApi);

  const [contributor, functional, technical] = await Promise.all(
    [
      ContactType.Contributor,
      ContactType.Functional,
      ContactType.Technical,
    ].map(async (contactType) => {
      const newPage = await context.newPage();
      const contact = await addContactToIntegration(contactType, id, newPage);
      await newPage.close();
      return contact;
    })
  );

  // Go to detail page and see if the contacts are visible
  await page.goto(`/admin/resources/integrations/${id}`);
  await expect(page.getByText(functional.email)).toBeVisible();
  await expect(page.getByText(contributor.email)).toBeVisible();
  await expect(page.getByText(technical.email)).toBeVisible();
});
