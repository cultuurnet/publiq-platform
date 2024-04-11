import { expect, test } from "@playwright/test";
import { KeyVisibility } from "@app-types/KeyVisibility";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { giveContactKeyVisibility } from "../admin/give-contact-key-visibility.js";

test("As an integrator with migrated projects the key visibility is v1 when creating a new integration", async ({
  browser,
}) => {
  const adminContext = await browser.newContext({
    storageState: "playwright/.auth/admin.json",
  });
  const adminPage = await adminContext.newPage();

  await giveContactKeyVisibility(
    adminPage,
    process.env.E2E_TEST_EMAIL!,
    KeyVisibility.v1
  );

  const userContext = await browser.newContext({
    storageState: "playwright/.auth/user.json",
  });
  const userPage = await userContext.newPage();

  const { integrationName, integrationId } =
    await createIntegrationAsIntegrator(userPage, IntegrationType.SearchApi);

  await adminPage.goto(`/admin/resources/integrations/${integrationId}`);
  await expect(adminPage.getByText("Key Visibilityv1")).toBeVisible();
});
