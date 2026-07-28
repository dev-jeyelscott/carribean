import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

test("a small desktop wheel gesture moves between homepage sections", async ({
    page,
}) => {
    await page.goto("/");

    const pager = page.locator("[data-home-section-pager]");
    const hero = page.locator("#home");
    const featured = page.locator("#featured");

    await expect(hero).toBeVisible();
    await expect(featured).toBeVisible();

    /*
     * The homepage navigation module is dynamically imported. Visible
     * server-rendered sections do not guarantee that its desktop wheel
     * observer has already been attached.
     *
     * Wait for the application-owned readiness contract before dispatching
     * the single wheel event so CI cannot lose the gesture during module
     * initialization.
     */
    await expect(pager).toHaveAttribute(
        "data-home-section-navigation",
        "ready",
        {
            timeout: 10_000,
        },
    );

    /*
     * Confirm that test instrumentation has also initialized before the
     * gesture. This catches an incomplete navigation setup immediately rather
     * than allowing the later movement assertion to fail ambiguously.
     */
    await expect(pager).toHaveAttribute("data-home-snap-count", "0");

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 120);

    /*
     * The counter is updated by the navigation module when it accepts a
     * gesture. This provides a deterministic application signal while the
     * position assertion below verifies the user-visible result.
     */
    await expect(pager).toHaveAttribute(
        "data-home-snap-count",
        "1",
        {
            timeout: 5_000,
        },
    );

    await expect
        .poll(
            async () =>
                Math.abs(
                    await featured.evaluate(
                        (element) =>
                            element.getBoundingClientRect().top,
                    ),
                ),
            {
                timeout: 5_000,
            },
        )
        .toBeLessThan(24);

    await page.mouse.wheel(0, -120);

    await expect
        .poll(
            async () =>
                Math.abs(
                    await hero.evaluate(
                        (element) =>
                            element.getBoundingClientRect().top,
                    ),
                ),
            {
                timeout: 5_000,
            },
        )
        .toBeLessThan(24);
});

test("reduced-motion users retain normal homepage content", async ({
    browser,
}) => {
    const context = await browser.newContext({
        reducedMotion: "reduce",
        viewport: {
            width: 1440,
            height: 900,
        },
    });

    const page = await context.newPage();

    await page.goto("/");

    await expect(page.locator("#home")).toBeVisible();
    await expect(page.locator("#featured")).toBeVisible();

    await expect(page.locator("html")).not.toHaveClass(
        /home-section-snap-active/,
    );

    await context.close();
});
