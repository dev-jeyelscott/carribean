import { expect, test } from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
});

/**
 * Verify the desktop sidebar, four-column layout, category navigation,
 * and compact card controls.
 */
test("desktop menu provides sticky categories and compact ordering cards", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await page.goto("/menu");

    const menuPage = page.locator("[data-menu-page]");
    const sidebar = page.locator("[data-menu-sidebar]");
    const mobileCategories = page.locator(
        "[data-menu-mobile-categories]",
    );

    await expect(menuPage).toBeVisible();
    await expect(menuPage).toHaveAttribute(
        "data-menu-navigation",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(sidebar).toBeVisible();
    await expect(mobileCategories).toBeHidden();

    const firstGrid = page.locator(
        "[data-menu-card-grid]",
    ).first();

    await expect(firstGrid).toBeVisible();

    const desktopColumnCount = await firstGrid.evaluate(
        (element) =>
            window
                .getComputedStyle(element)
                .gridTemplateColumns
                .split(" ")
                .filter(Boolean)
                .length,
    );

    expect(desktopColumnCount).toBe(4);

    const firstCard = page.locator("[data-menu-card]").first();

    await expect(firstCard).toBeVisible();
    await expect(
        firstCard.locator("[data-menu-quantity]"),
    ).toBeVisible();

    const quantityValue = firstCard.locator(
        "[data-menu-quantity-value]",
    );

    await expect(quantityValue).toHaveText("1");

    await firstCard
        .locator("[data-menu-quantity-increase]")
        .click();

    await expect(quantityValue).toHaveText("2");

    const desktopCategoryLinks = page.locator(
        '[data-menu-category-link][data-menu-navigation-position="desktop"]',
    );

    test.skip(
        (await desktopCategoryLinks.count()) < 2,
        "The seeded menu requires at least two visible categories.",
    );

    const secondCategoryLink = desktopCategoryLinks.nth(1);
    const targetHash = await secondCategoryLink.getAttribute(
        "href",
    );

    await secondCategoryLink.click();

    await expect(secondCategoryLink).toHaveAttribute(
        "aria-current",
        "true",
    );

    expect(page.url()).toContain(targetHash ?? "");
});

/**
 * Verify that mobile replaces the sidebar with a sticky horizontal selector
 * and uses a one-column card grid.
 */
test("mobile menu uses a sticky horizontal category selector", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 390,
        height: 844,
    });

    await page.goto("/menu");

    const menuPage = page.locator("[data-menu-page]");

    await expect(menuPage).toHaveAttribute(
        "data-menu-navigation",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(
        page.locator("[data-menu-sidebar]"),
    ).toBeHidden();

    const mobileCategories = page.locator(
        "[data-menu-mobile-categories]",
    );

    await expect(mobileCategories).toBeVisible();

    const categoryPosition = await mobileCategories.evaluate(
        (element) => window.getComputedStyle(element).position,
    );

    expect(categoryPosition).toBe("sticky");

    const firstGrid = page.locator(
        "[data-menu-card-grid]",
    ).first();

    const mobileColumnCount = await firstGrid.evaluate(
        (element) =>
            window
                .getComputedStyle(element)
                .gridTemplateColumns
                .split(" ")
                .filter(Boolean)
                .length,
    );

    expect(mobileColumnCount).toBe(1);
});

/**
 * Verify that reduced-motion mode retains immediate native category
 * navigation without depending on GSAP.
 */
test("reduced motion keeps menu category navigation usable", async ({
    page,
}) => {
    await page.emulateMedia({
        reducedMotion: "reduce",
    });

    await page.setViewportSize({
        width: 1440,
        height: 900,
    });

    await page.goto("/menu");

    const categoryLinks = page.locator(
        '[data-menu-category-link][data-menu-navigation-position="desktop"]',
    );

    test.skip(
        (await categoryLinks.count()) < 2,
        "The seeded menu requires at least two visible categories.",
    );

    const secondCategoryLink = categoryLinks.nth(1);

    await secondCategoryLink.click();

    await expect(secondCategoryLink).toHaveAttribute(
        "aria-current",
        "true",
    );

    await expect(page.locator("html")).not.toHaveClass(
        /home-section-snap-active/,
    );
});
