import {
    expect,
    test,
} from "@playwright/test";

test.use({
    reducedMotion: "reduce",
});

/**
 * Verify every rendered card remains inside its assigned carousel item.
 */
test("desktop menu cards remain inside their carousel slots", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await page.goto("/menu");

    const menuPage = page.locator("[data-menu-page]");

    await expect(menuPage).toBeVisible();

    await expect(menuPage).toHaveAttribute(
        "data-menu-experience",
        "ready",
        {
            timeout: 10_000,
        },
    );

    const firstTrack = page.locator(
        "[data-menu-carousel-track]",
    ).first();

    await expect(firstTrack).toBeVisible();

    const overflowViolations = await firstTrack
        .locator("[data-menu-carousel-item]")
        .evaluateAll((items) => {
            return items.slice(0, 4).filter((item) => {
                const card = item.querySelector(
                    "[data-menu-card]",
                );

                if (!(card instanceof HTMLElement)) {
                    return true;
                }

                const slotBounds =
                    item.getBoundingClientRect();

                const cardBounds =
                    card.getBoundingClientRect();

                return (
                    cardBounds.left < slotBounds.left - 1
                    || cardBounds.right > slotBounds.right + 1
                    || cardBounds.width > slotBounds.width + 1
                );
            }).length;
        });

    expect(overflowViolations).toBe(0);
});

/**
 * Verify a card targets the reusable Livewire product modal without
 * navigating away from the menu.
 */
test("menu card opens and closes the reusable product modal", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1440,
        height: 950,
    });

    await page.goto("/menu");

    const firstCard = page.locator(
        "[data-product-modal-trigger]",
    ).first();

    await expect(firstCard).toBeVisible();

    const originalUrl = page.url();

    await firstCard.click();

    const dialog = page.locator(
        "[data-product-modal]",
    );

    await expect(dialog).toBeVisible({
        timeout: 10_000,
    });

    await expect(dialog).toHaveAttribute(
        "open",
        "",
    );

    await expect(
        dialog.locator("[data-product-modal-panel]"),
    ).toBeVisible();

    expect(page.url()).toBe(originalUrl);

    await dialog.locator(
        "[data-product-modal-close-action]",
    ).click();

    await expect(dialog).toBeHidden({
        timeout: 10_000,
    });
});
