import { expect, test } from "@playwright/test";

const sectionIds = [
    "island-roots",
    "values",
    "heritage",
    "experience",
    "invitation",
];

/**
 * Return one panel's viewport-relative top position.
 */
async function panelTop(
    page,
    sectionId: string,
): Promise<number> {
    return page
        .locator(`#${sectionId}`)
        .evaluate((element) =>
            Math.round(
                element.getBoundingClientRect().top,
            ),
        );
}

/**
 * Return one panel's rendered viewport height.
 */
async function panelHeight(
    page,
    sectionId: string,
): Promise<number> {
    return page
        .locator(`#${sectionId}`)
        .evaluate((element) =>
            Math.round(
                element.getBoundingClientRect().height,
            ),
        );
}

test.describe("About page section navigation", () => {
    test.use({
        viewport: {
            width: 1440,
            height: 900,
        },
    });

    test("aligns every desktop section to the viewport top", async ({
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

        /*
         * Verify the hero's primary internal action uses the same exact
         * navigation behavior as the fixed section pager.
         */
        await page
            .locator(
                '#about-hero a[href="#island-roots"]',
            )
            .first()
            .click();

        await expect
            .poll(() => panelTop(page, "island-roots"))
            .toBe(0);

        await expect(root).toHaveAttribute(
            "data-about-active-section",
            "island-roots",
        );

        /*
         * The previous hero must finish exactly at the viewport top.
         * No portion of it should remain visible above the story section.
         */
        const heroBottom = await page
            .locator("#about-hero")
            .evaluate((element) =>
                Math.round(
                    element.getBoundingClientRect().bottom,
                ),
            );

        expect(heroBottom).toBe(0);

        const viewportHeight = await page.evaluate(
            () => window.innerHeight,
        );

        for (const sectionId of sectionIds) {
            await page
                .locator(
                    `[data-about-section-link][href="#${sectionId}"]`,
                )
                .click();

            await expect
                .poll(() => panelTop(page, sectionId))
                .toBe(0);

            await expect(root).toHaveAttribute(
                "data-about-active-section",
                sectionId,
            );

            expect(
                await panelHeight(page, sectionId),
            ).toBe(viewportHeight);
        }
    });

    test("keeps every section readable with reduced motion", async ({
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
            .poll(() => panelTop(page, "heritage"))
            .toBe(0);

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
                            window.getComputedStyle(element);

                        return (
                            styles.opacity === "0" ||
                            styles.visibility === "hidden"
                        );
                    }).length,
                );

        expect(hiddenAnimatedElements).toBe(0);
    });
});
