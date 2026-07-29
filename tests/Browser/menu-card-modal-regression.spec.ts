import {
    expect,
    test,
    type Page,
} from "@playwright/test";

type ReducedMotionPreference =
    | "reduce"
    | "no-preference";

/**
 * Load the menu using the requested operating-system motion preference and
 * wait until the actual menu listeners have been initialized.
 */
async function loadMenu(
    page: Page,
    reducedMotion: ReducedMotionPreference = "no-preference",
): Promise<void> {
    await page.emulateMedia({
        reducedMotion,
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
}

/**
 * Verify normal motion preferences initialize every interactive menu system.
 */
test("normal motion initializes the menu experience", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await loadMenu(page);

    const firstCarousel = page.locator(
        "[data-menu-carousel]",
    ).first();

    await expect(firstCarousel).toHaveAttribute(
        "data-menu-carousel-ready",
        "true",
    );

    await expect(
        page.locator(
            '[data-menu-navigation-position="desktop"]',
        ).first(),
    ).toHaveAttribute(
        "aria-current",
        "true",
    );
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

    await loadMenu(page);

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
 * Verify a card targets the reusable Livewire modal without navigating away.
 */
test("menu card opens and closes the reusable product modal", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1440,
        height: 950,
    });

    await loadMenu(page);

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

/**
 * Verify the seeded six-item Signature Entrées carousel moves in both
 * directions and keeps its accessible status synchronized.
 */
test("carousel next and previous controls move the menu track", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await loadMenu(page);

    const section = page.locator(
        "#category-signature-entrees",
    );

    await section.scrollIntoViewIfNeeded();

    const track = section.locator(
        "[data-menu-carousel-track]",
    );

    const previousButton = section.locator(
        "[data-menu-carousel-previous]",
    );

    const nextButton = section.locator(
        "[data-menu-carousel-next]",
    );

    const status = section.locator(
        "[data-menu-carousel-status]",
    );

    await expect(nextButton).toBeEnabled();
    await expect(previousButton).toBeDisabled();
    await expect(status).toHaveText("1–4 of 6");

    const initialScrollLeft = await track.evaluate(
        (element) => element.scrollLeft,
    );

    await nextButton.click();

    await expect
        .poll(async () => {
            return track.evaluate(
                (element) => element.scrollLeft,
            );
        })
        .toBeGreaterThan(initialScrollLeft + 20);

    await expect(previousButton).toBeEnabled();
    await expect(status).toHaveText("2–5 of 6");

    await previousButton.click();

    await expect
        .poll(async () => {
            return track.evaluate(
                (element) => element.scrollLeft,
            );
        })
        .toBeLessThanOrEqual(2);

    await expect(previousButton).toBeDisabled();
    await expect(status).toHaveText("1–4 of 6");
});

/**
 * Verify ScrollTrigger updates the active category and the desktop category
 * panel remains pinned beneath the shared header.
 */
test("scrolling updates the active category and keeps the sidebar sticky", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await loadMenu(page);

    const targetSection = page.locator(
        "#category-signature-entrees",
    );

    await targetSection.evaluate((element) => {
        element.scrollIntoView({
            behavior: "auto",
            block: "center",
        });
    });

    const activeLink = page.locator(
        '[data-menu-navigation-position="desktop"]'
        + '[href="#category-signature-entrees"]',
    );

    await expect(activeLink).toHaveAttribute(
        "aria-current",
        "true",
        {
            timeout: 5_000,
        },
    );

    const sidebar = page.locator(
        "[data-menu-sidebar]",
    );

    await expect(sidebar).toBeVisible();

    const sidebarTop = await sidebar.evaluate(
        (element) => element.getBoundingClientRect().top,
    );

    expect(sidebarTop).toBeGreaterThanOrEqual(88);
    expect(sidebarTop).toBeLessThanOrEqual(136);
});

/**
 * Verify reduced-motion visitors retain every menu interaction while GSAP
 * removes non-essential movement.
 */
test("reduced motion retains modal and carousel functionality", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1440,
        height: 950,
    });

    await loadMenu(page, "reduce");

    const firstCarousel = page.locator(
        "[data-menu-carousel]",
    ).first();

    await expect(firstCarousel).toHaveAttribute(
        "data-menu-carousel-ready",
        "true",
    );

    const firstCard = page.locator(
        "[data-product-modal-trigger]",
    ).first();

    await firstCard.click();

    const dialog = page.locator(
        "[data-product-modal]",
    );

    await expect(dialog).toBeVisible({
        timeout: 10_000,
    });

    await dialog.locator(
        "[data-product-modal-close-action]",
    ).click();

    await expect(dialog).toBeHidden({
        timeout: 10_000,
    });
});
