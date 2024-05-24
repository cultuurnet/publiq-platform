import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";

test.use({ storageState: "playwright/.auth/user.json" });

test("As an integrator I can create a new widget integration", async ({
  page,
  context,
}) => {
  const { integrationName } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.Widgets
  );
  await expect(page.getByText(integrationName)).toBeVisible();

  // nieuwe
  const pagePromise = context.waitForEvent("page");
  await page
    .getByText(/^TestWidgets bouwen$/)
    .locator("button")
    .click();
  const newPage = await pagePromise;
  await expect(
    newPage.getByRole("button", { name: "Nieuwe widget maken" })
  ).toBeVisible();
});
