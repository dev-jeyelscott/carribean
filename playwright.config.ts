import {
    defineConfig,
    devices,
} from '@playwright/test';

const baseUrl =
    process.env.PLAYWRIGHT_BASE_URL
    ?? 'http://127.0.0.1:8000';

export default defineConfig({
    testDir: './tests/Browser',

    fullyParallel: false,

    forbidOnly: Boolean(
        process.env.CI,
    ),

    retries: process.env.CI
        ? 1
        : 0,

    workers: 1,

    reporter: [
        ['line'],
        [
            'html',
            {
                open: 'never',
            },
        ],
    ],

    use: {
        baseURL: baseUrl,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },

    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],

    webServer: {
        command:
            'php artisan serve --env=browser --host=127.0.0.1 --port=8000',
        url: baseUrl,
        reuseExistingServer:
            ! process.env.CI,
        timeout: 120_000,
        stdout: 'pipe',
        stderr: 'pipe',
    },
});
