import { defineConfig, devices } from "@playwright/test";

const isCi = Boolean(process.env.CI);

/**
 * Keep local browser acceptance traffic separate from:
 *
 * - Carribean's Sail web server on container port 80
 * - Any local Artisan development server on port 8000
 *
 * CI continues using port 8000 because its environment is isolated.
 */
const browserServerPort = process.env.PLAYWRIGHT_PORT ?? (isCi ? "8000" : "81");

const baseUrl =
    process.env.PLAYWRIGHT_BASE_URL ?? `http://127.0.0.1:${browserServerPort}`;

export default defineConfig({
    testDir: "./tests/Browser",

    fullyParallel: false,

    forbidOnly: isCi,

    retries: isCi ? 1 : 0,

    workers: 1,

    reporter: [
        ["line"],
        [
            "html",
            {
                open: "never",
            },
        ],
    ],

    use: {
        baseURL: baseUrl,
        trace: "on-first-retry",
        screenshot: "only-on-failure",
        video: "retain-on-failure",
    },

    projects: [
        {
            name: "chromium",
            use: {
                ...devices["Desktop Chrome"],
            },
        },
    ],

    webServer: {
        command: [
            "php artisan serve",
            "--env=browser",
            "--host=127.0.0.1",
            `--port=${browserServerPort}`,
        ].join(" "),

        url: baseUrl,

        // Browser acceptance must always use its dedicated environment.
        // Never reuse a stale development or test server.
        reuseExistingServer: false,

        timeout: 120_000,
        stdout: "pipe",
        stderr: "pipe",
    },
});
