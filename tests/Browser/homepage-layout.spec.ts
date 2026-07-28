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
            scrollSnapType:
                window.getComputedStyle(document.documentElement)
                    .scrollSnapType,
        };
    });

    expect(
        Math.abs(metrics.heroHeight - metrics.expectedHeroHeight),
    ).toBeLessThanOrEqual(2);

    expect(metrics.highlightSpacing).toBeGreaterThanOrEqual(32);
    expect(metrics.scrollSnapType).toBe("none");
});

/**
 * Verify that a small desktop wheel gesture advances from the homepage hero
 * to the restaurant-highlight section beneath the sticky header.
 */
test("slight hero scroll advances to the next section", async ({ page }) => {
    await page.goto("/");

    const hero = page.locator("[data-public-hero]");
    const highlights = page.locator(
        '[aria-label="Restaurant highlights"]',
    );

    await expect(hero).toBeVisible();
    await expect(highlights).toBeVisible();

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 40);

    await expect
        .poll(async () => {
            return highlights.evaluate((element) => {
                const header = document.querySelector("header");

                if (!(header instanceof HTMLElement)) {
                    throw new Error("Sticky header was not found.");
                }

                return Math.abs(
                    element.getBoundingClientRect().top
                    - header.getBoundingClientRect().height,
                );
            });
        })
        .toBeLessThanOrEqual(6);

    const sectionScrollPosition = await page.evaluate(() => window.scrollY);

    /*
     * The observer listens only on the hero, so scrolling from the highlights
     * must return to ordinary browser behavior instead of snapping again.
     */
    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 240);

    await expect
        .poll(async () => {
            return page.evaluate(() => window.scrollY);
        })
        .toBeGreaterThan(sectionScrollPosition + 100);
});

/**
 * Verify that users requesting reduced motion retain ordinary page scrolling
 * without an automatic hero-to-section transition.
 */
test("reduced motion keeps native homepage scrolling", async ({ page }) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.goto("/");

    const highlights = page.locator(
        '[aria-label="Restaurant highlights"]',
    );

    await expect(highlights).toBeVisible();

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 40);

    await expect
        .poll(async () => {
            return page.evaluate(() => window.scrollY);
        })
        .toBeGreaterThan(0);

    const distanceFromHeader = await highlights.evaluate((element) => {
        const header = document.querySelector("header");

        if (!(header instanceof HTMLElement)) {
            throw new Error("Sticky header was not found.");
        }

        return Math.abs(
            element.getBoundingClientRect().top
            - header.getBoundingClientRect().height,
        );
    });

    /*
     * A small native wheel movement must not move the entire viewport directly
     * to the next homepage section.
     */
    expect(distanceFromHeader).toBeGreaterThan(200);
});
