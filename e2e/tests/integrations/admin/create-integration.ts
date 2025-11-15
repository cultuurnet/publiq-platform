import { expect, Page } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import { IntegrationType } from "@app-types/IntegrationType";

const IntegrationTypeSubscriptionMap: Record<IntegrationType, string> = {
  [IntegrationType.SearchApi]: "b46745a1-feb5-45fd-8fa9-8e3ef25aac26",
  [IntegrationType.Widgets]: "c470ccbf-074c-4bf1-b526-47c94c5e9296",
  [IntegrationType.EntryApi]: "6311ba66-91c2-4905-a182-150f1cdf4825",
  [IntegrationType.UiTPAS]: "52bb667f-d4da-47cd-9a76-f8896be410bd",
};

export async function createIntegration(page: Page, type: IntegrationType) {
  // Make the integration
  const name = faker.word.adjective();
  await page.goto("/admin/resources/integrations");
  await page.getByRole("link", { name: "Integrations" }).click();
  await page.getByRole("link", { name: "Create Integration" }).click();
  await page.getByPlaceholder("Name").fill(name);
  await page.locator("#type").selectOption(type);
  await page.locator("#key_visibility").selectOption("all");
  await page.getByPlaceholder("Description").fill(faker.lorem.lines(2));
  await page
    .locator("[dusk='subscriptions-select']")
    .selectOption(IntegrationTypeSubscriptionMap[type]);
  await page.getByPlaceholder("Website").fill(faker.internet.url());
  await page.getByRole("button", { name: "Create Integration" }).click();

  await expect(
    page.locator("h1").getByText(`Integration Details: ${name}`)
  ).toBeVisible();

  const integrationUrl = page.url();
  const id = integrationUrl.split("/").pop()!;

  return { name, id };
}
