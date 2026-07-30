import { expect, test, type Page } from "@playwright/test";

const desktopViewport = {
    width: 1728,
    height: 864,
};

/**
 * Return the fixed public header's visible lower edge.
 */
async function headerBottom(page: Page): Promise<number> {
    return page
        .locator("[data-public-header-shell] > header")
        .evaluate((element) =>
            Math.round(element.getBoundingClientRect().bottom),
        );
}

/**
 * Return one About panel's viewport-relative top edge.
 */
async function panelTop(page: Page, panelId: string): Promise<number> {
    return page.locator(`#${panelId}`).evaluate((element) =>
        Math.round(element.getBoundingClientRect().top),
    );
}

/**
 * Wait until one About panel is aligned directly beneath the compact header.
 */
async function expectPanelAligned(page: Page, panelId: string): Promise<void> {
    await expect
        .poll(async () => {
            const [headerEdge, sectionTop] = await Promise.all([
                headerBottom(page),
                panelTop(page, panelId),
            ]);

            return Math.abs(sectionTop - headerEdge);
        })
        .toBeLessThanOrEqual(2);
}

test.describe("About page desktop scroll navigation", () => {
    test.use({ viewport: desktopViewport });

    test("moves one section per wheel gesture and runs section reveals", async ({
        page,
    }) => {
        await page.goto("/about");

        const root = page.locator("[data-about-page]");

        await expect(root).toHaveAttribute(
            "data-about-section-navigation",
            "ready",
        );
        await expect(root).toHaveAttribute("data-about-motion", "ready");
        await expect(root).toHaveAttribute("data-about-snap-count", "0");

        await page.mouse.wheel(0, 160);

        await expectPanelAligned(page, "island-roots");
        await expect(root).toHaveAttribute(
            "data-about-active-section",
            "island-roots",
        );
        await expect(root).toHaveAttribute("data-about-snap-count", "1");
        await expect(page.locator("#island-roots")).toHaveAttribute(
            "data-about-animation-state",
            "complete",
        );

        /*
         * A single trackpad-style momentum burst must not skip two sections.
         */
        await page.mouse.wheel(0, 180);
        await page.mouse.wheel(0, 180);
        await page.mouse.wheel(0, 180);

        await expectPanelAligned(page, "values");
        await expect(root).toHaveAttribute(
            "data-about-active-section",
            "values",
        );
        await expect(root).toHaveAttribute("data-about-snap-count", "2");
        await expect(page.locator("#values")).toHaveAttribute(
            "data-about-animation-state",
            "complete",
        );

        await page.mouse.wheel(0, -180);

        await expectPanelAligned(page, "island-roots");
        await expect(root).toHaveAttribute(
            "data-about-active-section",
            "island-roots",
        );

        /*
         * Previously revealed content must stay visible when scrolling back.
         */
        const hiddenTargets = await page
            .locator("#island-roots [data-about-reveal]")
            .evaluateAll((elements) =>
                elements.filter((element) => {
                    const styles = window.getComputedStyle(element);

                    return (
                        styles.opacity === "0" ||
                        styles.visibility === "hidden"
                    );
                }).length,
            );

        expect(hiddenTargets).toBe(0);
    });

    test("keeps native scrolling on a short desktop viewport", async ({
        page,
    }) => {
        await page.setViewportSize({
            width: 1280,
            height: 700,
        });
        await page.goto("/about");

        const root = page.locator("[data-about-page]");

        await expect(root).toHaveAttribute(
            "data-about-section-navigation",
            "ready",
        );

        await page.mouse.wheel(0, 240);

        await expect
            .poll(() => page.evaluate(() => window.scrollY))
            .toBeGreaterThan(0);

        await expect(root).toHaveAttribute("data-about-snap-count", "0");
    });
});
