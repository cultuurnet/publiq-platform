import { expect, type Page } from "@playwright/test";
import { IntegrationStatus } from "@app-types/IntegrationStatus";
import { Environment } from "@app-types/Environment";

const StatusToCopy: Record<IntegrationStatus, string> = {
  [IntegrationStatus.Active]: "Active",
  [IntegrationStatus.Blocked]: "Blocked",
  [IntegrationStatus.Deleted]: "Deleted",
  [IntegrationStatus.Draft]: "Draft",
  [IntegrationStatus.PendingApprovalIntegration]: "PendingApprovalIntegration",
  [IntegrationStatus.PendingApprovalPayment]: "PendingApprovalPayment",
};

export const assertKeyVisibility = async (
  page: Page,
  integrationStatus: IntegrationStatus
) => {
  await expect(
    page.locator('[data-relationship="uiTiDv1Consumers"]')
  ).toBeVisible();

  // polling is required since the keys don't get updated with Fetch/XHR calls
  await expect(async () => {
    await page.reload();

    const uiTiDv1Consumers = page.locator(
      '[data-relationship="uiTiDv1Consumers"]'
    );
    const auth0Clients = page.locator('[data-relationship="auth0Clients"]');
    const keycloakClients = page.locator(
      '[data-relationship="keycloakClients"]'
    );

    const tables = [uiTiDv1Consumers, auth0Clients, keycloakClients];

    const assertions = tables.map((table) => {
      return Object.values(Environment).map(async (environment) => {
        const row = table.getByRole("row", {
          name: `${environment} ${StatusToCopy[integrationStatus]}`,
        });
        await expect(row).toBeVisible({ timeout: 7_000 });
      });
    });

    await Promise.all(assertions.flat());
  }).toPass({
    intervals: [1_000, 2_000, 3_000],
  });
};
