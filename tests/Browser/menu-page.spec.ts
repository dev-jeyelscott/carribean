import {
    expect,
    test,
    type Locator,
} from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
});

/**
 * Count cards fully visible inside one horizontal carousel viewport.
 */
async function fullyVisibleCardCount(
    track: Locator,
): Promise<number> {
    return track.evaluate((trackElement) => {
        const trackBounds =
            trackElement.getBoundingClientRect();

        const items = [
            ...trackElement.querySelectorAll(
                "[data-menu-carousel-item]",
            ),
        ];

        return items.filter((item) => {
            const bounds =
                item.getBoundingClientRect();

            return (
                bounds.left >= trackBounds.left - 1
                && bounds.right <= trackBounds.right + 1
            );
        }).length;
    });
}

/**
 * Select the minimum valid values for every required modal option group.
 */
async function selectRequiredOptions(
    modal: Locator,
): Promise<void> {
    const requiredGroups = modal.locator(
        '[data-menu-option-group][data-required="true"]',
    );

    const groupCount =
        await requiredGroups.count();

    for (
        let groupIndex = 0;
        groupIndex < groupCount;
        groupIndex += 1
    ) {
        const group = requiredGroups.nth(groupIndex);

        const minimumSelections = Number.parseInt(
            (await group.getAttribute(
                "data-minimum",
            )) ?? "1",
            10,
        );

        const radios = group.locator(
            'input[type="radio"][value]:not([value=""])',
        );

        if (await radios.count()) {
            await radios.first().check();

            continue;
        }

        const checkboxes = group.locator(
            'input[type="checkbox"]',
        );

        for (
            let optionIndex = 0;
            optionIndex < minimumSelections;
            optionIndex += 1
        ) {
            await checkboxes.nth(optionIndex).check();
        }
    }
}

test("desktop menu provides immersive category carousels", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1600,
        height: 1000,
    });

    await page.goto("/menu");

    const menuPage = page.locator(
        "[data-menu-page]",
    );

    await expect(menuPage).toBeVisible();

    await expect(menuPage).toHaveAttribute(
        "data-menu-experience",
        "ready",
        {
            timeout: 10_000,
        },
    );

    await expect(
        page.locator("[data-menu-sidebar]"),
    ).toBeVisible();

    await expect(
        page.locator(
            "[data-menu-mobile-categories]",
        ),
    ).toBeHidden();

    const firstSection = page.locator(
        "[data-menu-section]",
    ).first();

    const sectionBounds =
        await firstSection.boundingBox();

    expect(sectionBounds).not.toBeNull();

    expect(sectionBounds?.height ?? 0)
        .toBeGreaterThanOrEqual(800);

    const firstTrack = page.locator(
        "[data-menu-carousel-track]",
    ).first();

    await expect(firstTrack).toBeVisible();

    expect(
        await fullyVisibleCardCount(firstTrack),
    ).toBe(4);

    const firstSectionCards = firstSection.locator(
        "[data-menu-carousel-item]",
    );

    if ((await firstSectionCards.count()) > 4) {
        const nextButton = firstSection.locator(
            "[data-menu-carousel-next]",
        );

        await expect(nextButton).toBeEnabled();

        const initialScrollLeft =
            await firstTrack.evaluate(
                (element) =>
                    element.scrollLeft,
            );

        await nextButton.click();

        await expect
            .poll(async () =>
                firstTrack.evaluate(
                    (element) =>
                        element.scrollLeft,
                ),
            )
            .toBeGreaterThan(initialScrollLeft);

        const afterButtonScroll =
            await firstTrack.evaluate(
                (element) =>
                    element.scrollLeft,
            );

        await firstTrack.focus();
        await page.keyboard.press("ArrowRight");

        await expect
            .poll(async () =>
                firstTrack.evaluate(
                    (element) =>
                        element.scrollLeft,
                ),
            )
            .toBeGreaterThan(afterButtonScroll);
    }

    const categoryLinks = page.locator(
        '[data-menu-category-link][data-menu-navigation-position="desktop"]',
    );

    if ((await categoryLinks.count()) >= 2) {
        const secondCategory = categoryLinks.nth(1);

        await secondCategory.click();

        await expect(secondCategory).toHaveAttribute(
            "aria-current",
            "true",
        );

        const targetHash =
            await secondCategory.getAttribute("href");

        expect(page.url()).toContain(
            targetHash ?? "",
        );
    }
});

test("product modal configures and adds an item without leaving the menu", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 1440,
        height: 950,
    });

    await page.goto("/menu");

    const firstSection = page.locator(
        "[data-menu-section]",
    ).first();

    await firstSection.scrollIntoViewIfNeeded();

    const sectionScrollBefore = await page.evaluate(
        () => window.scrollY,
    );

    await firstSection.locator(
        "[data-product-modal-trigger]",
    ).first().click();

    const dialog = page.locator(
        "[data-product-modal]",
    );

    await expect(dialog).toBeVisible({
        timeout: 10_000,
    });

    await expect(dialog).toHaveAttribute("open", "");

    await selectRequiredOptions(dialog);

    const total = dialog.locator(
        "[data-product-modal-total]",
    );

    const totalBefore =
        (await total.textContent())?.trim() ?? "";

    await dialog.locator(
        '[data-product-quantity] button[aria-label^="Increase"]',
    ).click();

    await expect(total).not.toHaveText(totalBefore);

    const addButton = dialog.locator(
        "[data-product-add]",
    );

    await expect(addButton).toBeEnabled();

    await addButton.click();

    await expect(dialog).toBeHidden({
        timeout: 10_000,
    });

    const notification = page.locator(
        "[data-public-notification]",
    );

    await expect(notification).toBeVisible();

    await expect(notification).toContainText(
        "Added to cart",
    );

    await expect(
        page.locator(
            '[aria-label$="in cart"]',
        ).first(),
    ).toHaveAttribute(
        "aria-label",
        /[1-9]\d* items? in cart/,
    );

    const sectionScrollAfter = await page.evaluate(
        () => window.scrollY,
    );

    expect(
        Math.abs(
            sectionScrollAfter - sectionScrollBefore,
        ),
    ).toBeLessThan(30);
});

test("mobile menu keeps native scrolling and a usable product modal", async ({
    page,
}) => {
    await page.setViewportSize({
        width: 390,
        height: 844,
    });

    await page.goto("/menu");

    await expect(
        page.locator("[data-menu-page]"),
    ).toHaveAttribute(
        "data-menu-experience",
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

    expect(
        await mobileCategories.evaluate(
            (element) =>
                window.getComputedStyle(element)
                    .position,
        ),
    ).toBe("sticky");

    const firstTrack = page.locator(
        "[data-menu-carousel-track]",
    ).first();

    const visibleCount =
        await fullyVisibleCardCount(firstTrack);

    expect(visibleCount).toBeGreaterThanOrEqual(1);
    expect(visibleCount).toBeLessThanOrEqual(2);

    const maximumScroll =
        await firstTrack.evaluate(
            (element) =>
                element.scrollWidth
                - element.clientWidth,
        );

    if (maximumScroll > 0) {
        await firstTrack.evaluate((element) => {
            element.scrollLeft = Math.min(
                160,
                element.scrollWidth
                    - element.clientWidth,
            );
        });

        await expect
            .poll(async () =>
                firstTrack.evaluate(
                    (element) =>
                        element.scrollLeft,
                ),
            )
            .toBeGreaterThan(0);
    }

    await page.locator(
        "[data-product-modal-trigger]",
    ).first().click();

    const dialog = page.locator(
        "[data-product-modal]",
    );

    await expect(dialog).toBeVisible();

    await expect(
        dialog.locator(
            "[data-product-modal-footer]",
        ),
    ).toBeVisible();

    await dialog.locator(
        "[data-product-modal-close-action]",
    ).click();

    await expect(dialog).toBeHidden();
});

test("reduced motion keeps native menu interaction", async ({
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

    await expect(
        page.locator("[data-menu-page]"),
    ).toHaveAttribute(
        "data-menu-experience",
        "ready",
        {
            timeout: 10_000,
        },
    );

    const firstCard = page.locator(
        "[data-menu-card]",
    ).first();

    await expect(firstCard).toBeVisible();

    expect(
        await firstCard.evaluate(
            (element) =>
                window.getComputedStyle(element)
                    .opacity,
        ),
    ).toBe("1");

    const categoryLinks = page.locator(
        '[data-menu-category-link][data-menu-navigation-position="desktop"]',
    );

    if ((await categoryLinks.count()) >= 2) {
        const secondCategory = categoryLinks.nth(1);

        await secondCategory.click();

        await expect(secondCategory).toHaveAttribute(
            "aria-current",
            "true",
        );
    }

    await page.locator(
        "[data-product-modal-trigger]",
    ).first().click();

    const dialog = page.locator(
        "[data-product-modal]",
    );

    await expect(dialog).toBeVisible();

    const modalTransform = await dialog.locator(
        "[data-product-modal-panel]",
    ).evaluate(
        (element) =>
            window.getComputedStyle(element)
                .transform,
    );

    expect([
        "none",
        "matrix(1, 0, 0, 1, 0, 0)",
    ]).toContain(modalTransform);
});
