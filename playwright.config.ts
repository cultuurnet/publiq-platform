/// <reference path="./node_modules/@types/node/index.d.ts" />
import "dotenv/config";
import { defineConfig, devices } from "@playwright/test";

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: "e2e",
  timeout: 60 * 1000,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  // Disable retries both locally and in CI
  retries: 0,
  workers: 1,
  expect: {
    timeout: 10000,
  },
  // In CI, also generate the HTML report so it can be uploaded as an artifact.
  reporter: process.env.CI
    ? [['list'], ['junit', { outputFile: './e2e/test-results.xml' }], ['html']]
    : [['html']],
  use: {
    baseURL: process.env.E2E_TEST_BASE_URL,
    // Capture as much context as possible when something fails in CI.
    // Traces, screenshots and videos will be available in artifacts.
    trace: process.env.CI ? 'retain-on-failure' : 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
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
      testMatch: "tests/**/*.test.ts",
    },
  ],
});
