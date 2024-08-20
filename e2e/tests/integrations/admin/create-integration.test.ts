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
  const contributrorEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole("link", { name: "Create Contact" }).click();
  await page.getByPlaceholder("Email").fill(contributrorEmail);
  await page.locator("#type").selectOption(type);
  await page.getByPlaceholder("First Name").fill(faker.person.firstName());
  await page.getByPlaceholder("Last Name").fill(faker.person.lastName());
  await page
    .locator("[dusk='integrations-select']")
    .selectOption(integrationId!);
  await page.getByRole("button", { name: "Create Contact" }).click();

  await page.waitForURL(/\/admin\/resources\/contacts\/(?!new).+$/, {
    waitUntil: "networkidle",
  });
  await expect(
    page.getByText(`Contact Details: ${contributrorEmail}`)
  ).toBeVisible({ timeout: 10_000 });
  await expect(page.getByText(type)).toBeVisible();
  return contributrorEmail;
}

test.use({ storageState: "playwright/.auth/admin.json" });

test("create a new integration as an admin (with functional, technical and contributor contact)", async ({
  page,
}) => {
  const { id } = await createIntegration(page, IntegrationType.SearchApi);

  const contributorEmail = await addContactToIntegration(
    ContactType.Contributor,
    id,
    page
  );
  const functionalEmail = await addContactToIntegration(
    ContactType.Functional,
    id,
    page
  );
  const technicalEmail = await addContactToIntegration(
    ContactType.Technical,
    id,
    page
  );

  // Go to overview page and see if the contacts are visible
  await page.goto(`/admin/resources/integrations/${id}`);
  await expect(page.getByText(functionalEmail)).toBeVisible();
  await expect(page.getByText(contributorEmail)).toBeVisible();
  await expect(page.getByText(technicalEmail)).toBeVisible();
});
