import { test as setup } from "@playwright/test";

setup("authenticate as admin", async ({ page }) => {
  await page.goto("/nl");

  await page.getByRole("link", { name: "Probeer gratis" }).click();

  await page.waitForURL(/account-acc.uitid.be\/*/);

  await page
    .getByLabel("Je e-mailadres")
    .fill(process.env.E2E_TEST_ADMIN_EMAIL!);
  await page
    .getByLabel("Je wachtwoord")
    .fill(process.env.E2E_TEST_ADMIN_PASSWORD!);

  await page.getByRole("button", { name: "Meld je aan", exact: true }).click();

  await page.waitForLoadState("networkidle");
  await page.waitForURL("/nl/integraties");

  await page.context().storageState({ path: "playwright/.auth/admin.json" });
});

setup("authenticate as contributor", async ({ page }) => {
  await page.goto("/nl");

  await page.getByRole("link", { name: "Probeer gratis" }).click();

  await page.waitForURL(/account-acc.uitid.be\/*/);

  await page.getByLabel("Je e-mailadres").fill(process.env.E2E_TEST_EMAIL!);
  await page.getByLabel("Je wachtwoord").fill(process.env.E2E_TEST_PASSWORD!);

  await page.getByRole("button", { name: "Meld je aan", exact: true }).click();

  await page.waitForLoadState("networkidle");
  await page.waitForURL("/nl/integraties");

  await page.context().storageState({ path: "playwright/.auth/user.json" });
});

setup(
  "authenticate as contributor with v1 key visibility",
  async ({ page }) => {
    await page.goto("/nl");

    await page.getByRole("link", { name: "Probeer gratis" }).click();

    await page.waitForURL(/account-acc.uitid.be\/*/);

    await page
      .getByLabel("Je e-mailadres")
      .fill(process.env.E2E_TEST_V1_EMAIL!);
    await page
      .getByLabel("Je wachtwoord")
      .fill(process.env.E2E_TEST_V1_PASSWORD!);

    await page
      .getByRole("button", { name: "Meld je aan", exact: true })
      .click();

    await page.waitForLoadState("networkidle");
    await page.waitForURL("/nl/integraties");

    await page
      .context()
      .storageState({ path: "playwright/.auth/user-v1.json" });
  }
);
