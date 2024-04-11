import { expect, Page } from "@playwright/test";
import { KeyVisibility } from "@app-types/KeyVisibility";

export const giveContactKeyVisibility = async (
  page: Page,
  contactEmail: string,
  keyVisibility: KeyVisibility
) => {
  const searchParams = new URLSearchParams({
    // include trashed items in search
    "contact-key-visibilities_trashed": "with",
    "contact-key-visibilities_page": "1",
    "contact-key-visibilities_search": contactEmail,
  });

  await page.goto(
    `/admin/resources/contact-key-visibilities?${searchParams.toString()}`
  );

  const hasKeyVisibilityForContact = !(await page
    .getByText("No Contacts Key Visibility matched the given criteria.")
    .isVisible());

  if (hasKeyVisibilityForContact) {
    const restoreButton = page.getByLabel("Restore");

    // restore item if it was trashed
    if (await restoreButton.isVisible()) {
      await restoreButton.click();
      await page.getByTestId("confirm-button").click();
    }

    await page.getByText(contactEmail).click();
    await page.getByRole("button", { name: "Actions" }).click();
    await page.locator(".z-\\[999\\]").click();
    await page.getByTestId("edit-resource").click();
    await page
      .getByRole("button", { name: "Update Contacts Key Visibility" })
      .click();
    await page.locator("#key_visibility").selectOption(keyVisibility);
    await page
      .getByRole("button", { name: "Update Contacts Key Visibility" })
      .click();
  } else {
    // create new Contacts Key Visibility
    await page
      .getByRole("link", { name: "Create Contacts Key Visibility" })
      .click();
    await page.getByPlaceholder("Email").fill(contactEmail);
    await page.locator("#key_visibility").selectOption(keyVisibility);
    await page
      .getByRole("button", { name: "Create Contacts Key Visibility" })
      .click();
  }
};
