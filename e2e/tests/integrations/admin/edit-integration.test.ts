import { expect, test } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import {
  createIntegration,
} from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can edit an existing integration", async ({ page }) => {
  const { page: integrationPage, name: integrationName } =
    await createIntegration(page, IntegrationType.SearchApi);

  await expect(
    page.locator("h1").getByText(`Integration Details: ${integrationName}`)
  ).toBeVisible();

  const url = integrationPage.url();
  const integrationId = url.split("/").pop();

  
  await page.goto(`/admin/resources/integrations/${integrationId}`);

  const newIntegrationName = faker.word.adjective();

  await page.locator("[dusk='edit-resource-button']").click();
  await page.getByPlaceholder('Name').fill(newIntegrationName);
  await page.getByPlaceholder('Description').fill(faker.lorem.lines(3));
  await page.getByRole('button', { name: 'Update Integration' }).click();

  await expect(
    page.locator("h1").getByText(`Integration Details: ${newIntegrationName}`)
  ).toBeVisible();

  await page.screenshot({ path: `screenshots/edit-integration-${integrationId}.png` });

});
