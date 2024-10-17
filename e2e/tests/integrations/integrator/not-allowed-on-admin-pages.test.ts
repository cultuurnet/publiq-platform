import { expect, test } from "@playwright/test";

test.use({ storageState: "playwright/.auth/user.json" });

test("Integrator access to Admin Page - Expect 403", async ({ page }) => {
  const response = await page.goto("/admin");
  const status = response?.status();
  expect(status).toBe(403);
  await expect(page.getByRole('heading', { name: "403" })).toBeVisible();
});
