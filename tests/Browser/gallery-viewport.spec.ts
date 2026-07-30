import {
    expect,
    test,
    type Page,
} from "@playwright/test";

test.use({
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Read the Gallery collection geometry from the rendered desktop viewport.
 */
async function readCollectionGeometry(page: Page) {
    return page.evaluate(() => {
        const header = document.querySelector(
            "[data-public-header-shell] > header",
        );

        const section = document.getElementById(
            "gallery-collection",
        );

        const inner = section?.querySelector(
            ".gallery-collection__inner",
        );

        const sheet = section?.querySelector(
            ".gallery-contact-sheet",
        );

        if (
            !(header instanceof HTMLElement)
            || !(section instanceof HTMLElement)
            || !(inner instanceof HTMLElement)
            || !(sheet instanceof HTMLElement)
        ) {
            return null;
        }

        const headerBottom =
            header.getBoundingClientRect().bottom;

        const sectionRect =
            section.getBoundingClientRect();

        return {
            cardCount: sheet.querySelectorAll(
                "[data-gallery-item]",
            ).length,
            innerOverflowPixels: Math.max(
                0,
                inner.scrollHeight
                    - inner.clientHeight,
            ),
            innerOverflowY:
                window.getComputedStyle(inner).overflowY,
            panelHeightDelta: Math.abs(
                sectionRect.height
                    - (
                        window.innerHeight
                        - headerBottom
                    ),
            ),
            panelTopDelta: Math.abs(
                sectionRect.top
                    - headerBottom,
            ),
            sheetOverflowPixels: Math.max(
                0,
                sheet.scrollHeight
                    - sheet.clientHeight,
            ),
        };
    });
}

test("gallery collection fits one viewport without nested vertical scrolling", async ({
    page,
}) => {
    await page.goto("/gallery");

    const pager = page.locator(
        '[data-section-pager-context="gallery"]',
    );

    await expect(pager).toHaveAttribute(
        "data-section-pager-state",
        /ready|reduced/,
    );

    await pager
        .locator('[href="#gallery-collection"]')
        .click();

    await expect(page).toHaveURL(
        /#gallery-collection$/,
    );

    await expect
        .poll(async () => {
            const geometry =
                await readCollectionGeometry(page);

            return geometry?.panelTopDelta
                ?? Number.POSITIVE_INFINITY;
        })
        .toBeLessThan(3);

    const geometry =
        await readCollectionGeometry(page);

    expect(geometry).not.toBeNull();

    if (geometry === null) {
        return;
    }

    expect(geometry.cardCount).toBeGreaterThan(0);
    expect(geometry.cardCount).toBeLessThanOrEqual(5);
    expect(geometry.panelHeightDelta).toBeLessThan(3);
    expect(geometry.innerOverflowY).toBe("clip");
    expect(geometry.innerOverflowPixels).toBeLessThanOrEqual(2);
    expect(geometry.sheetOverflowPixels).toBeLessThanOrEqual(2);
});
