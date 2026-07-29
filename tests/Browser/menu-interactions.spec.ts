import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Verify that the menu hero initializes its GSAP ScrollTrigger enhancement.
 */
test("menu hero initializes scroll-linked motion", async ({ page }) => {
    await page.goto("/menu");

    const hero = page.locator("[data-menu-hero]");

    await expect(hero).toBeVisible();

    await expect(hero).toHaveAttribute(
        "data-menu-hero-scroll-trigger",
        "ready",
        {
            timeout: 10_000,
        },
    );

    const depthLayer = hero.locator("[data-menu-hero-depth]").first();

    const initialTransform = await depthLayer.evaluate((element) =>
        window.getComputedStyle(element).transform,
    );

    await page.mouse.wheel(0, 500);

    await expect
        .poll(
            async () =>
                depthLayer.evaluate((element) =>
                    window.getComputedStyle(element).transform,
                ),
            {
                timeout: 5_000,
            },
        )
        .not.toBe(initialTransform);
});

/**
 * Verify that desktop carousel controls advance by four cards.
 */
test("menu carousel controls advance one visible page", async ({ page }) => {
    await page.goto("/menu");

    const section = page
        .locator("[data-menu-section]")
        .filter({
            has: page.locator(
                "[data-menu-carousel-next]:not([disabled])",
            ),
        })
        .first();

    await expect(section).toBeVisible();

    const carousel = section.locator("[data-menu-carousel]");
    const status = section.locator("[data-menu-carousel-status]");
    const next = section.locator("[data-menu-carousel-next]");
    const previous = section.locator("[data-menu-carousel-previous]");

    await expect(carousel).toHaveAttribute(
        "data-menu-carousel-ready",
        "true",
        {
            timeout: 10_000,
        },
    );

    await expect(status).toContainText("1–4");

    await next.click();

    await expect(status).toContainText("5–8");

    await previous.click();

    await expect(status).toContainText("1–4");
});

/**
 * Verify that the active desktop category remains readable and is not
 * truncated.
 */
test("active desktop category is wide and readable", async ({ page }) => {
    await page.goto("/menu");

    const activeCategory = page.locator(
        '[data-menu-navigation-position="desktop"][aria-current="true"]',
    );

    await expect(activeCategory).toBeVisible();

    const styles = await activeCategory.evaluate((element) => {
        const label = element.querySelector(
            ".menu-category-label",
        );

        if (!(label instanceof HTMLElement)) {
            throw new Error("Category label was not found.");
        }

        const linkStyles = window.getComputedStyle(element);
        const labelStyles = window.getComputedStyle(label);

        return {
            color: linkStyles.color,
            labelOverflow: labelStyles.overflow,
            labelTextOverflow: labelStyles.textOverflow,
            labelWhiteSpace: labelStyles.whiteSpace,
            width: element.getBoundingClientRect().width,
        };
    });

    expect(styles.color).toBe("rgb(255, 255, 255)");
    expect(styles.labelTextOverflow).not.toBe("ellipsis");
    expect(styles.labelWhiteSpace).toBe("normal");
    expect(styles.width).toBeGreaterThanOrEqual(240);
});

/**
 * Verify that reduced-motion visitors do not receive scroll-linked hero
 * transforms.
 */
test("reduced motion disables menu hero scroll animation", async ({ page }) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.goto("/menu");

    const hero = page.locator("[data-menu-hero]");
    const depthLayer = hero.locator("[data-menu-hero-depth]").first();

    await expect(hero).toBeVisible();

    const initialTransform = await depthLayer.evaluate((element) =>
        window.getComputedStyle(element).transform,
    );

    await page.mouse.wheel(0, 500);

    const finalTransform = await depthLayer.evaluate((element) =>
        window.getComputedStyle(element).transform,
    );

    expect(finalTransform).toBe(initialTransform);
});
