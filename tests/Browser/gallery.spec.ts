import { expect, test } from "@playwright/test";

test("gallery filtering and native image viewer remain usable", async ({
    page,
}) => {
    await page.goto("/gallery");

    const gallery = page.locator("[data-gallery-page]");
    const cards = page.locator("[data-gallery-open]");

    await expect(gallery).toBeVisible();
    await expect(gallery).toHaveAttribute(
        "data-gallery-motion-state",
        /ready|reduced/,
    );
    await expect(cards).toHaveCount(3);

    await cards.first().click();

    const dialog = page.locator("[data-gallery-dialog]");
    const dialogTitle = page.locator("[data-gallery-dialog-title]");
    const firstTitle = await dialogTitle.textContent();

    await expect(dialog).toBeVisible();
    await expect(dialogTitle).not.toBeEmpty();

    await page.keyboard.press("ArrowRight");

    await expect
        .poll(async () => dialogTitle.textContent())
        .not.toBe(firstTitle);

    await page.keyboard.press("Escape");
    await expect(dialog).not.toBeVisible();

    await page
        .locator('[data-gallery-filter][data-gallery-category="dish"]')
        .click();

    await expect(page).toHaveURL(/category=dish/);
    await expect(page.locator("[data-gallery-open]")).toHaveCount(2);
    await expect(
        page.locator(
            '[data-gallery-filter][data-gallery-category="dish"]',
        ),
    ).toHaveAttribute("aria-current", "true");
});
