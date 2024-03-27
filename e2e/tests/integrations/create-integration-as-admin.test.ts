import { expect, test } from "@playwright/test";
import { faker } from '@faker-js/faker';

test.use({ storageState: 'playwright/.auth/admin.json' });

test("create a new integration as an admin (with functional, technical and contributor contact)", async ({ page }) => {
  
  // Make the integration
  const name = faker.word.adjective();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Integrations" }).click();
  await page.getByRole("link", { name: "Create Integration" }).click();
  await page.getByPlaceholder("Name").fill(name);
  await page.locator("#type").selectOption("entry-api");
  await page.locator("#key_visibility").selectOption("all");
  await page
    .getByPlaceholder("Description")
    .fill(faker.lorem.lines(2));
  await page
    .getByTestId("subscriptions")
    .selectOption("b46745a1-feb5-45fd-8fa9-8e3ef25aac26");
  await page.getByRole("button", { name: "Create Integration" }).click();
  await expect(
    page
      .locator("h1")
      .getByText(`Integration Details: ${name}`)
  ).toBeVisible();

  const url = page.url()
  const integrationId = url.split('/').pop();

  // Add contributor contact
  const contributrorEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole('link', { name: 'Create Contact' }).click();
  await page.getByPlaceholder('Email').fill(contributrorEmail);
  await page.locator('#type').selectOption('contributor');
  await page.getByPlaceholder('First Name').fill(faker.person.firstName());
  await page.getByPlaceholder('Last Name').fill(faker.person.lastName());
  await page.getByTestId('integrations').selectOption(integrationId!);
  await page.getByRole('button', { name: 'Create Contact' }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/);
  await expect(page.getByRole('heading', { name: `Contact Details: ${contributrorEmail}` })).toBeVisible();
  await expect(page.getByText('contributor')).toBeVisible();

  // Add functional contact
  const functionalEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole('link', { name: 'Create Contact' }).click();
  await page.getByPlaceholder('Email').fill(functionalEmail!);
  await page.locator('#type').selectOption('functional');
  await page.getByPlaceholder('First Name').fill(faker.person.firstName());
  await page.getByPlaceholder('Last Name').fill(faker.person.lastName());
  await page.getByTestId('integrations').selectOption(integrationId!);
  await page.getByRole('button', { name: 'Create Contact' }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/);
  await expect(page.getByRole('heading', { name: `Contact Details: ${functionalEmail}` })).toBeVisible();
  await expect(page.getByText('functional')).toBeVisible();

  // Add technical contact
  const technicalEmail = faker.internet.email();
  await page.goto("/admin");
  await page.getByRole("link", { name: "Contacts", exact: true }).click();
  await page.getByRole('link', { name: 'Create Contact' }).click();
  await page.getByPlaceholder('Email').fill(technicalEmail!);
  await page.locator('#type').selectOption('technical');
  await page.getByPlaceholder('First Name').fill(faker.person.firstName());
  await page.getByPlaceholder('Last Name').fill(faker.person.lastName());
  await page.getByTestId('integrations').selectOption(integrationId!);
  await page.getByRole('button', { name: 'Create Contact' }).click();

  await page.waitForURL(/https?:\/\/[^/]*\/admin\/resources\/contacts\/(\/.*)?/);
  await expect(page.getByRole('heading', { name: `Contact Details: ${technicalEmail}` })).toBeVisible();
  await expect(page.getByText('technical')).toBeVisible();

  // Go to overview page and see if the contacts are visible
  await page.goto(`/admin/resources/integrations/${integrationId}`);
  await expect(page.getByText(functionalEmail)).toBeVisible();
  await expect(page.getByText(contributrorEmail)).toBeVisible();
  await expect(page.getByText(technicalEmail)).toBeVisible();
});
