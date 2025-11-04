/// <reference path="./node_modules/@types/node/index.d.ts" />
import "dotenv/config";
import { defineConfig, devices } from "@playwright/test";

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: "e2e",
  timeout: 3 * 60 * 1000,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  expect: {
    timeout: 10000,
  },
  reporter: process.env.CI
    ? [['list'], ['junit', { outputFile: './e2e/test-results.xml' }]]
    : [['html']],
  use: {
    baseURL: process.env.E2E_TEST_BASE_URL,
    trace: "on-first-retry",
  },
  projects: [
    {
      name: "setup",
      testDir: "e2e/setup",
      testMatch: "setup/**/*.setup.ts",
    },
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        viewport: { width: 1920, height: 1080 },
        storageState: "playwright/.auth/user.json",
      },
      dependencies: ["setup"],
      testMatch: "tests/integrations/integrator/create-integration.test.ts",
    },
  ],
});
