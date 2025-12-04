import { expect, test } from "@playwright/test";
import { createIntegration } from "./create-integration.js";
import { createOrganization } from "./create-organization.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { assertKeyVisibility } from "./assert-key-visibility.js";
import { IntegrationStatus } from "@app-types/IntegrationStatus";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can activate an integration", async ({ page }) => {
  page.on('console', msg => console.log('BROWSER LOG:', msg.text()));
  page.on('pageerror', err => console.log('BROWSER ERROR:', err.message));

  const testTargetUrl = `/admin/resources/integrations/`;
  await page.goto(testTargetUrl);

  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000); // Give Nova time to mount

  await page.screenshot({ path: 'test-results/nova-page.png' });

  // create organization
  const { id: organizationId } = await createOrganization(page);

  // create integration
  const { id: integrationId } = await createIntegration(
    page,
    IntegrationType.SearchApi
  );

  // activate integration
  const targetUrl = `/admin/resources/integrations/${integrationId}`;
  if (!page.url().endsWith(targetUrl)) {
    await page.goto(targetUrl);
  }

  await page.locator(`[dusk="${integrationId}-control-selector"]`).click();
  await page.getByRole("button", { name: "Activate Integration" }).click();
  await page.locator("#organization").selectOption(organizationId);
  await page.locator("[dusk='confirm-action-button']").click();

  await expect(
    page.locator(`a[href="/admin/resources/organizations/${organizationId}"]`)
  ).toBeVisible();
  await expect(page.getByText('Statusactive', { exact: true })).toBeVisible();

  await assertKeyVisibility(page, IntegrationStatus.Active);
});
