import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

test("homepage header becomes compact away from the page top", async ({
    page,
}) => {
    await page.goto("/");

    const header = page.locator('body[data-page="home"] header');

    await expect(header).toHaveAttribute(
        "data-public-header-state",
        "expanded",
        {
            timeout: 10_000,
        },
    );

    const expandedHeight = await header.evaluate(
        (element) => element.getBoundingClientRect().height,
    );

    await page.evaluate(() => {
        window.scrollTo(0, window.innerHeight);
    });

    await expect(header).toHaveAttribute(
        "data-public-header-state",
        "compact",
    );

    await expect(header).toHaveClass(/public-header-compact/);

    await expect
        .poll(
            async () =>
                header.evaluate(
                    (element) => element.getBoundingClientRect().height,
                ),
            {
                timeout: 5_000,
            },
        )
        .toBeLessThan(expandedHeight - 10);

    await page.evaluate(() => {
        window.scrollTo(0, 0);
    });

    await expect(header).toHaveAttribute(
        "data-public-header-state",
        "expanded",
    );

    await expect(header).not.toHaveClass(/public-header-compact/);

    await expect
        .poll(
            async () =>
                header.evaluate(
                    (element) => element.getBoundingClientRect().height,
                ),
            {
                timeout: 5_000,
            },
        )
        .toBeGreaterThan(expandedHeight - 5);
});
