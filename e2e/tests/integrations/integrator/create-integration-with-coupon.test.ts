import { test, expect } from "@playwright/test";
import { createIntegrationAsIntegrator } from "./create-integration.js";
import { IntegrationType } from "@app-types/IntegrationType";
import { createCoupon } from "../admin/create-coupon.js";

test("As an integrator I can create an integration with coupon (so it doesn't need admin approval)", async ({
  browser,
}) => {
  const adminContext = await browser.newContext({
    storageState: "playwright/.auth/admin.json",
  });

  const adminPage = await adminContext.newPage();

  const { couponCode } = await createCoupon(adminPage);

  const userContext = await browser.newContext({
    storageState: "playwright/.auth/user.json",
  });

  const userPage = await userContext.newPage();

  const { integrationName, integrationId } =
    await createIntegrationAsIntegrator(
      userPage,
      IntegrationType.SearchApi,
      couponCode
    );

  await userPage.waitForURL(`/nl/integraties/${integrationId}`);
  await expect(userPage.getByText(integrationName)).toBeVisible();
  await userPage.waitForLoadState("networkidle");

  await expect(userPage.getByText("Actief", { exact: true })).toBeVisible();
});
