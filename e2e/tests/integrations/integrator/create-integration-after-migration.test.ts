import { expect, test } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";

test("As an integrator with migrated projects the key visibility is v1 when creating a new integration", async ({
  browser,
}) => {
  const userContext = await browser.newContext({
    storageState: "playwright/.auth/user-v1.json",
  });
  const userPage = await userContext.newPage();

  const { integrationId } = await createIntegrationAsIntegrator(
    userPage,
    IntegrationType.SearchApi
  );

  const adminContext = await browser.newContext({
    storageState: "playwright/.auth/admin.json",
  });
  const adminPage = await adminContext.newPage();
  await adminPage.goto(`/admin/resources/integrations/${integrationId}`);
  await expect(adminPage.getByText("Key Visibilityv1")).toBeVisible();
});
