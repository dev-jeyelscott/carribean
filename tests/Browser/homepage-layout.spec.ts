import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Verify that the homepage hero fills the full viewport while the transparent
 * fixed navigation overlays it.
 */
test("homepage hero fills the full viewport", async ({ page }) => {
    await page.goto("/");

    const hero = page.locator("#home");
    const featured = page.locator("#featured");

    await expect(hero).toBeVisible();
    await expect(featured).toBeVisible();

    const metrics = await page.evaluate(() => {
        const header = document.querySelector("header");
        const heroSection = document.querySelector("#home");
        const featuredSection = document.querySelector("#featured");

        if (
            !(header instanceof HTMLElement)
            || !(heroSection instanceof HTMLElement)
            || !(featuredSection instanceof HTMLElement)
        ) {
            throw new Error("Homepage layout elements were not found.");
        }

        const headerStyles = window.getComputedStyle(header);
        const heroRect = heroSection.getBoundingClientRect();
        const featuredRect = featuredSection.getBoundingClientRect();

        return {
            viewportHeight: window.innerHeight,
            headerPosition: headerStyles.position,
            heroTop: heroRect.top,
            heroHeight: heroRect.height,
            distanceBetweenPanels: featuredRect.top - heroRect.bottom,
            scrollSnapType:
                window.getComputedStyle(document.documentElement)
                    .scrollSnapType,
        };
    });

    /*
     * The homepage header overlays the hero instead of consuming document
     * height, so the hero must match the complete viewport.
     */
    expect(metrics.headerPosition).toBe("fixed");

    expect(Math.abs(metrics.heroTop)).toBeLessThanOrEqual(2);

    expect(
        Math.abs(metrics.heroHeight - metrics.viewportHeight),
    ).toBeLessThanOrEqual(2);

    /*
     * Full-screen panels remain normal server-rendered document content and
     * should follow one another without an artificial gap.
     */
    expect(Math.abs(metrics.distanceBetweenPanels)).toBeLessThanOrEqual(2);

    /*
     * GSAP owns enhanced desktop navigation. Native CSS scroll snapping must
     * remain disabled to prevent conflicting movement.
     */
    expect(metrics.scrollSnapType).toBe("none");
});

/**
 * Verify that a slight desktop wheel gesture advances from the hero to the
 * adjacent featured-dishes panel.
 */
test("slight hero scroll advances to the next section", async ({ page }) => {
    await page.goto("/");

    const pager = page.locator("[data-home-section-pager]");
    const hero = page.locator("#home");
    const featured = page.locator("#featured");

    await expect(hero).toBeVisible();
    await expect(featured).toBeVisible();

    /*
     * The navigation module is dynamically imported. Wait for the explicit
     * application-owned readiness state before dispatching the wheel event.
     */
    await expect(pager).toHaveAttribute(
        "data-home-section-navigation",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(pager).toHaveAttribute("data-home-snap-count", "0");

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 40);

    /*
     * A completed snap counter is more deterministic than waiting for an
     * arbitrary timeout while still verifying the actual panel position below.
     */
    await expect(pager).toHaveAttribute(
        "data-home-snap-count",
        "1",
        {
            timeout: 5_000,
        },
    );

    await expect(pager).toHaveAttribute(
        "data-home-active-section",
        "Featured dishes",
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
        .toBeLessThanOrEqual(24);
});

/**
 * Verify that reduced-motion users retain native document scrolling without
 * automatic full-panel navigation.
 */
test("reduced motion keeps native homepage scrolling", async ({ page }) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.goto("/");

    const pager = page.locator("[data-home-section-pager]");
    const featured = page.locator("#featured");

    await expect(featured).toBeVisible();

    await expect(pager).toHaveAttribute(
        "data-home-section-navigation",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(page.locator("html")).not.toHaveClass(
        /home-section-snap-active/,
    );

    await expect(pager).toHaveAttribute("data-home-snap-count", "0");

    await page.mouse.move(720, 450);
    await page.mouse.wheel(0, 40);

    await expect
        .poll(
            async () => page.evaluate(() => window.scrollY),
            {
                timeout: 5_000,
            },
        )
        .toBeGreaterThan(0);

    /*
     * Reduced-motion mode must not convert the small native wheel movement
     * into a complete GSAP panel transition.
     */
    await expect(pager).toHaveAttribute("data-home-snap-count", "0");

    const distanceFromViewportTop = await featured.evaluate((element) =>
        Math.abs(element.getBoundingClientRect().top),
    );

    expect(distanceFromViewportTop).toBeGreaterThan(200);
});
