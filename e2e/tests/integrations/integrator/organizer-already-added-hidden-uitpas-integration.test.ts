import { test, expect } from "@playwright/test";
import { IntegrationType } from "@app-types/IntegrationType";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { requestActivationAsIntegrator } from "./request-activation.js";
import { addOrganizer, ORGANIZER_NAME } from "./add-organizer.js";

test("As an integrator I no longer see an organizer in the search list once it is added", async ({
  page,
}) => {
  const { integrationId } = await createIntegrationAsIntegrator(
    page,
    IntegrationType.UiTPAS
  );
  await requestActivationAsIntegrator(
    page,
    integrationId,
    IntegrationType.UiTPAS
  );

  // Adding it proves the search returns this organizer for this query: the
  // helper only gets through by clicking it in the search list.
  await addOrganizer(page);

  await page.getByRole("button", { name: "Organisatie toevoegen" }).click();

  const searchResponse = page.waitForResponse((response) =>
    response.url().includes("/organizers?name=")
  );
  await page.getByRole("textbox").fill(ORGANIZER_NAME);
  await searchResponse;

  // The search list is empty both before the results arrive and after they are
  // filtered out, so there is no state change to wait for. Give the list a
  // moment to render what it got back before asserting it stayed empty.
  await page.waitForTimeout(1000);

  await expect(
    page.locator("li").filter({ hasText: ORGANIZER_NAME })
  ).toHaveCount(0);
});
