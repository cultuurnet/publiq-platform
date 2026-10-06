import { Page, expect } from "@playwright/test";

export const ORGANIZER_NAME = "publiq vzw - UiTPAS organizer";

export async function addOrganizer(page: Page, organizerName = ORGANIZER_NAME) {
  // Switching tabs is an Inertia visit that remounts the page. Clicking the
  // tab we are already on would still trigger one, and it lands while the
  // dialog is open, closing it again before the organizer list arrives.
  if (!page.url().includes("tab=organisations")) {
    await page.getByRole("button", { name: "Organisaties" }).click();
    await page.waitForURL(/tab=organisations/);
  }

  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();
  await page.getByRole("textbox").fill(organizerName);
  await page.locator("li").filter({ hasText: organizerName }).click();
  await page.getByRole("button", { name: "Bevestigen" }).click();
  await expect(
    page.getByRole("heading", { name: organizerName })
  ).toBeVisible();
}
