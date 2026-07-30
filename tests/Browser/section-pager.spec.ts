import { expect, test } from "@playwright/test";

test("gallery section pager navigates and tracks full-screen sections", async ({
    page,
}) => {
    await page.goto("/gallery");

    const pager = page.locator(
        '[data-section-pager][data-section-pager-context="gallery"]',
    );

    await expect(pager).toBeVisible();
    await expect(pager).toHaveAttribute(
        "data-section-pager-state",
        /ready|reduced/,
    );
    await expect(pager).toHaveAttribute(
        "data-section-pager-trigger-count",
        "4",
    );

    const heroLink = pager.locator(
        '[data-section-pager-link][href="#gallery-hero"]',
    );

    const signatureLink = pager.locator(
        '[data-section-pager-link][href="#gallery-signature"]',
    );

    const collectionLink = pager.locator(
        '[data-section-pager-link][href="#gallery-collection"]',
    );

    await expect(heroLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await signatureLink.click();

    await expect(page).toHaveURL(/#gallery-signature$/);
    await expect(signatureLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await expect
        .poll(async () => {
            return page.locator("#gallery-signature").evaluate(
                (section) => Math.abs(
                    section.getBoundingClientRect().top - 80,
                ),
            );
        })
        .toBeLessThan(20);

    await collectionLink.click();

    await expect(page).toHaveURL(/#gallery-collection$/);
    await expect(collectionLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await page.goBack();

    await expect(page).toHaveURL(/#gallery-signature$/);
    await expect(signatureLink).toHaveAttribute(
        "aria-current",
        "location",
    );
});
