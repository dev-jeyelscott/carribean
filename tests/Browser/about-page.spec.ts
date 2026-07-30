import {
    expect,
    test,
    type Page,
} from "@playwright/test";

const sectionIds = [
    "island-roots",
    "values",
    "heritage",
    "experience",
    "invitation",
];

/**
 * Return a section's viewport-relative geometry.
 */
async function sectionBounds(
    page: Page,
    sectionId: string,
): Promise<{
    top: number;
    bottom: number;
    height: number;
}> {
    return page
        .locator(`#${sectionId}`)
        .evaluate((element) => {
            const bounds =
                element.getBoundingClientRect();

            return {
                top: Math.round(bounds.top),
                bottom: Math.round(bounds.bottom),
                height: Math.round(bounds.height),
            };
        });
}

/**
 * Return the visible lower edge of the fixed public header.
 */
async function headerBottom(
    page: Page,
): Promise<number> {
    return page
        .locator(
            "[data-public-header-shell] > header",
        )
        .evaluate((element) =>
            Math.round(
                element.getBoundingClientRect().bottom,
            ),
        );
}

/**
 * Return the difference between a panel's top and the header's bottom.
 */
async function sectionHeaderGap(
    page: Page,
    sectionId: string,
): Promise<number> {
    const bounds = await sectionBounds(
        page,
        sectionId,
    );

    const headerEdge = await headerBottom(page);

    return Math.abs(bounds.top - headerEdge);
}

/**
 * Return the difference between a panel's bottom and viewport bottom.
 */
async function sectionViewportBottomGap(
    page: Page,
    sectionId: string,
): Promise<number> {
    const bounds = await sectionBounds(
        page,
        sectionId,
    );

    const viewportHeight = await page.evaluate(
        () => window.innerHeight,
    );

    return Math.abs(
        bounds.bottom - viewportHeight,
    );
}

test.describe("About page section navigation", () => {
    /*
     * This reproduces the viewport shown in the reported broken screenshot.
     * The previous 1440x900 test unintentionally bypassed the short-height bug.
     */
    test.use({
        viewport: {
            width: 1728,
            height: 864,
        },
    });

    test("fits every section beneath the fixed header", async ({
        page,
    }) => {
        await page.goto("/about");

        const root = page.locator(
            "[data-about-page]",
        );

        await expect(root).toHaveAttribute(
            "data-about-section-navigation",
            "ready",
        );

        await expect(root).toHaveAttribute(
            "data-about-motion",
            "ready",
        );

        const triggerCount = Number(
            await root.getAttribute(
                "data-about-trigger-count",
            ),
        );

        /*
         * Six active-section triggers, five section animation triggers, and
         * at least one parallax trigger must be registered.
         */
        expect(triggerCount).toBeGreaterThanOrEqual(12);

        await page
            .locator(
                '#about-hero a[href="#island-roots"]',
            )
            .first()
            .click();

        await expect
            .poll(() =>
                sectionHeaderGap(
                    page,
                    "island-roots",
                ),
            )
            .toBeLessThanOrEqual(2);

        await expect
            .poll(() =>
                sectionViewportBottomGap(
                    page,
                    "island-roots",
                ),
            )
            .toBeLessThanOrEqual(2);

        const storyHeaderEdge =
            await headerBottom(page);

        const heroBottom = await page
            .locator("#about-hero")
            .evaluate((element) =>
                Math.round(
                    element.getBoundingClientRect().bottom,
                ),
            );

        /*
         * The previous panel ends behind the opaque fixed header instead of
         * remaining visibly exposed above the story section.
         */
        expect(
            Math.abs(
                heroBottom - storyHeaderEdge,
            ),
        ).toBeLessThanOrEqual(2);

        await expect(
            page.locator("#island-roots"),
        ).toHaveAttribute(
            "data-about-animation-state",
            "complete",
            {
                timeout: 5_000,
            },
        );

        for (const sectionId of sectionIds) {
            await page
                .locator(
                    `[data-about-section-link][href="#${sectionId}"]`,
                )
                .click();

            await expect
                .poll(() =>
                    sectionHeaderGap(
                        page,
                        sectionId,
                    ),
                )
                .toBeLessThanOrEqual(2);

            await expect
                .poll(() =>
                    sectionViewportBottomGap(
                        page,
                        sectionId,
                    ),
                )
                .toBeLessThanOrEqual(2);

            await expect(root).toHaveAttribute(
                "data-about-active-section",
                sectionId,
            );

            await expect(
                page.locator(`#${sectionId}`),
            ).toHaveAttribute(
                "data-about-animation-state",
                "complete",
                {
                    timeout: 5_000,
                },
            );
        }
    });

    test("keeps every section visible with reduced motion", async ({
        page,
    }) => {
        await page.emulateMedia({
            reducedMotion: "reduce",
        });

        await page.goto("/about");

        const root = page.locator(
            "[data-about-page]",
        );

        await expect(root).toHaveAttribute(
            "data-about-motion",
            "reduced",
        );

        await page
            .locator(
                '[data-about-section-link][href="#heritage"]',
            )
            .click();

        await expect
            .poll(() =>
                sectionHeaderGap(
                    page,
                    "heritage",
                ),
            )
            .toBeLessThanOrEqual(2);

        await expect
            .poll(() =>
                sectionViewportBottomGap(
                    page,
                    "heritage",
                ),
            )
            .toBeLessThanOrEqual(2);

        await expect(
            page.locator("#heritage"),
        ).toHaveAttribute(
            "data-about-animation-state",
            "complete",
        );

        const hiddenAnimatedElements =
            await page
                .locator(
                    [
                        "#heritage [data-about-reveal]",
                        "#heritage [data-about-image-reveal]",
                        "#heritage [data-about-parallax] img",
                    ].join(", "),
                )
                .evaluateAll((elements) =>
                    elements.filter((element) => {
                        const styles =
                            window.getComputedStyle(
                                element,
                            );

                        return (
                            styles.opacity === "0" ||
                            styles.visibility ===
                                "hidden"
                        );
                    }).length,
                );

        expect(hiddenAnimatedElements).toBe(0);
    });
});
