import type { Page } from "@playwright/test";
import { fakerNL_BE as faker } from "@faker-js/faker";
import type { IntegrationType } from "../../types.js";

export const ContactTypes = {
    FUNCTIONAL: "functional",
    TECHNICAL: "technical",
    CONTRIBUTOR: "contributor",
  } as const;

  export type ContactType = typeof ContactTypes[keyof typeof ContactTypes];

  const IntegrationTypeSubscriptionMap = {
    'search-api': 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26',
    'widgets': 'c470ccbf-074c-4bf1-b526-47c94c5e9296',
    'entry-api': '6311ba66-91c2-4905-a182-150f1cdf4825',
  } as const;
  
  export async function createIntegration(page: Page, type: IntegrationType) {
    // Make the integration
    const name = faker.word.adjective();
    await page.goto("/admin");
    await page.getByRole("link", { name: "Integrations" }).click();
    await page.getByRole("link", { name: "Create Integration" }).click();
    await page.getByPlaceholder("Name").fill(name);
    await page.locator("#type").selectOption(type);
    await page.locator("#key_visibility").selectOption("all");
    await page.getByPlaceholder("Description").fill(faker.lorem.lines(2));
    await page
      .getByTestId("subscriptions")
      .selectOption(IntegrationTypeSubscriptionMap[type]);
    await page.getByRole("button", { name: "Create Integration" }).click();
    return {name, page};
  }