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

    const hero = page.locator("#home");
    const featured = page.locator("#featured");

    await expect(hero).toBeVisible();
    await expect(featured).toBeVisible();

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 120);

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
