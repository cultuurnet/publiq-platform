import { test, expect } from "@playwright/test";
import { createOrganization } from "./create-organization.js";

test.use({ storageState: "playwright/.auth/admin.json" });

test("As an admin I can create a new organization", async ({ page }) => {
  await createOrganization(page);
});
