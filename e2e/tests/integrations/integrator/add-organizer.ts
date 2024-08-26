import { Page, expect } from "@playwright/test";

export async function addOrganizer(
  page: Page,
  organizerName = "publiq vzw - UiTPAS organizer"
) {
  await page.getByRole("button", { name: "Organisaties" }).click();
  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();
  await page.getByRole("textbox").fill(organizerName);
  await page.locator("li").filter({ hasText: organizerName }).click();
  await page.getByRole("button", { name: "Bevestigen" }).click();
  await expect(
    page.getByRole("heading", { name: "publiq vzw - UiTPAS organizer" })
  ).toBeVisible();
}
