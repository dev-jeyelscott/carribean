import {
    expect,
    test,
    type Page,
} from "@playwright/test";

/**
 * Collect uncaught browser errors while one public page is exercised.
 */
function collectPageErrors(page: Page): string[] {
    const errors: string[] = [];

    page.on("pageerror", (error) => {
        errors.push(error.message);
    });

    return errors;
}

test("homepage renders without component errors", async ({ page }) => {
    const pageErrors = collectPageErrors(page);

    const response = await page.goto("/");

    expect(response?.ok()).toBeTruthy();

    await expect(page.locator("body")).toBeVisible();

    expect(pageErrors).toEqual([]);
});

test("gallery registers and runs its ScrollTriggers", async ({ page }) => {
    const pageErrors = collectPageErrors(page);

    const response = await page.goto("/gallery");

    expect(response?.ok()).toBeTruthy();

    const gallery = page.locator("[data-gallery-page]");

    await expect(gallery).toHaveAttribute(
        "data-gallery-motion-state",
        /ready|reduced/,
    );

    await expect
        .poll(async () => {
            const value = await gallery.getAttribute(
                "data-gallery-trigger-count",
            );

            return Number(value ?? 0);
        })
        .toBeGreaterThanOrEqual(3);

    const collection = page.locator("#gallery-collection");

    await collection.scrollIntoViewIfNeeded();

    await expect(collection).toHaveAttribute(
        "data-gallery-animation-state",
        "complete",
    );

    expect(pageErrors).toEqual([]);
});

test("contact registers and runs its ScrollTriggers", async ({ page }) => {
    const pageErrors = collectPageErrors(page);

    const response = await page.goto("/contact");

    expect(response?.ok()).toBeTruthy();

    const contact = page.locator("[data-contact-page]");

    await expect(contact).toHaveAttribute(
        "data-contact-motion",
        /ready|reduced/,
    );

    await expect
        .poll(async () => {
            const value = await contact.getAttribute(
                "data-contact-trigger-count",
            );

            return Number(value ?? 0);
        })
        .toBeGreaterThanOrEqual(3);

    const messageSection = page.locator("#contact-message");

    await messageSection.scrollIntoViewIfNeeded();

    await expect(messageSection).toHaveAttribute(
        "data-contact-animation-state",
        "complete",
    );

    expect(pageErrors).toEqual([]);
});

test("about how-we-work section contains no images", async ({ page }) => {
    const response = await page.goto("/about");

    expect(response?.ok()).toBeTruthy();

    await expect(page.locator("#experience img")).toHaveCount(0);
});
