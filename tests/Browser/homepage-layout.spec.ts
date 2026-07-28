import { expect, test } from "@playwright/test";

test.use({
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Verify that the homepage hero fills the viewport beneath the sticky header
 * and that the restaurant-highlight cards have visible separation.
 */
test("homepage hero fills the usable viewport", async ({ page }) => {
    await page.goto("/");

    const hero = page.locator("[data-public-hero]");
    const highlights = page.locator(
        '[aria-label="Restaurant highlights"]',
    );

    await expect(hero).toBeVisible();
    await expect(highlights).toBeVisible();

    const metrics = await page.evaluate(() => {
        const header = document.querySelector("header");
        const heroSection = document.querySelector("[data-public-hero]");
        const highlightPanel = document.querySelector(
            '[aria-label="Restaurant highlights"] > div',
        );

        if (
            !(header instanceof HTMLElement)
            || !(heroSection instanceof HTMLElement)
            || !(highlightPanel instanceof HTMLElement)
        ) {
            throw new Error("Homepage layout elements were not found.");
        }

        const headerRect = header.getBoundingClientRect();
        const heroRect = heroSection.getBoundingClientRect();
        const highlightPanelRect = highlightPanel.getBoundingClientRect();

        return {
            expectedHeroHeight: window.innerHeight - headerRect.height,
            heroHeight: heroRect.height,
            highlightSpacing:
                highlightPanelRect.top - heroRect.bottom,
            scrollSnapStop:
                window.getComputedStyle(heroSection).scrollSnapStop,
            scrollSnapType:
                window.getComputedStyle(document.documentElement)
                    .scrollSnapType,
        };
    });

    expect(
        Math.abs(metrics.heroHeight - metrics.expectedHeroHeight),
    ).toBeLessThanOrEqual(2);

    expect(metrics.highlightSpacing).toBeGreaterThanOrEqual(32);
    expect(metrics.scrollSnapType).toContain("y");
    expect(metrics.scrollSnapStop).toBe("always");
});

/**
 * Verify that users requesting reduced motion receive normal page scrolling
 * without homepage section snapping.
 */
test("homepage disables section snapping for reduced motion", async ({
    page,
}) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.goto("/");

    const scrollSnapType = await page.evaluate(() => {
        return window.getComputedStyle(document.documentElement)
            .scrollSnapType;
    });

    expect(scrollSnapType).toBe("none");
});
