import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Verify that the About page uses full-screen-ready sections while preserving
 * native document scrolling.
 */
test("about page uses full viewport sections", async ({ page }) => {
    await page.goto("/about");

    const root = page.locator("[data-about-page]");
    const hero = page.locator("#about-hero");
    const panels = page.locator("[data-about-panel]");

    await expect(root).toBeVisible();
    await expect(hero).toBeVisible();

    await expect(root).toHaveAttribute(
        "data-about-motion",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(panels).toHaveCount(6);

    const metrics = await page.evaluate(() => {
        const heroSection = document.querySelector("#about-hero");
        const aboutPanels = [
            ...document.querySelectorAll("[data-about-panel]"),
        ];

        if (!(heroSection instanceof HTMLElement)) {
            throw new Error("About hero was not found.");
        }

        return {
            viewportHeight: window.innerHeight,
            heroHeight: heroSection.getBoundingClientRect().height,
            panelHeights: aboutPanels.map(
                (panel) => panel.getBoundingClientRect().height,
            ),
            horizontalOverflow:
                document.documentElement.scrollWidth
                - document.documentElement.clientWidth,
            scrollSnapType: window.getComputedStyle(
                document.documentElement,
            ).scrollSnapType,
        };
    });

    expect(
        Math.abs(metrics.heroHeight - metrics.viewportHeight),
    ).toBeLessThanOrEqual(2);

    metrics.panelHeights.forEach((height) => {
        expect(height).toBeGreaterThanOrEqual(
            metrics.viewportHeight - 2,
        );
    });

    expect(metrics.horizontalOverflow).toBeLessThanOrEqual(1);
    expect(metrics.scrollSnapType).toBe("none");

    await page.locator("#values").scrollIntoViewIfNeeded();

    await expect(root).toHaveAttribute(
        "data-about-active-section",
        "values",
        {
            timeout: 5_000,
        },
    );
});

/**
 * Verify that reduced-motion users receive visible content and native
 * scrolling without parallax or reveal transitions.
 */
test("reduced motion keeps about content visible", async ({ page }) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.goto("/about");

    const root = page.locator("[data-about-page]");

    await expect(root).toBeVisible();

    await expect(root).toHaveAttribute(
        "data-about-motion",
        "reduced",
        {
            timeout: 10_000,
        },
    );

    const hiddenElements = await page
        .locator("[data-about-reveal]")
        .evaluateAll((elements) =>
            elements.filter((element) => {
                const styles = window.getComputedStyle(element);

                return (
                    styles.visibility === "hidden"
                    || Number.parseFloat(styles.opacity) === 0
                );
            }).length,
        );

    expect(hiddenElements).toBe(0);

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 180);

    await expect
        .poll(
            async () => page.evaluate(() => window.scrollY),
            {
                timeout: 5_000,
            },
        )
        .toBeGreaterThan(0);
});
