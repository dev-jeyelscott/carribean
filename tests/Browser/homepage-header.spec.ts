import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

const sharedHeaderRoutes = [
    "/",
    "/menu",
    "/cart",
];

for (const route of sharedHeaderRoutes) {
    test(`shared public header renders on ${route}`, async ({
        page,
    }) => {
        await page.goto(route);

        const header = page.locator(
            "[data-public-header-shell] > header",
        );

        await expect(header).toHaveCount(1);

        await expect(header).toHaveAttribute(
            "data-public-header-state",
            "expanded",
            {
                timeout: 10_000,
            },
        );

        await expect(
            header.locator(
                'nav[aria-label="Primary navigation"]',
            ),
        ).toHaveCount(1);

        const position = await header.evaluate(
            (element) =>
                window.getComputedStyle(element).position,
        );

        expect(position).toBe("fixed");
    });
}

test("shared public header becomes compact after scrolling", async ({
    page,
}) => {
    await page.goto("/menu");

    const header = page.locator(
        "[data-public-header-shell] > header",
    );

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

    await expect(header).toHaveClass(
        /public-header-compact/,
    );

    await expect
        .poll(
            async () =>
                header.evaluate(
                    (element) =>
                        element.getBoundingClientRect().height,
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

    await expect(header).not.toHaveClass(
        /public-header-compact/,
    );
});
