import { expect, test, type Page } from "@playwright/test";

const desktopViewport = {
    width: 1728,
    height: 864,
};

/**
 * Return the fixed public header's visible lower edge.
 */
async function headerBottom(page: Page): Promise<number> {
    return page
        .locator("[data-public-header-shell] > header")
        .evaluate((element) =>
            Math.round(element.getBoundingClientRect().bottom),
        );
}

/**
 * Return one Contact section's viewport-relative top edge.
 */
async function sectionTop(
    page: Page,
    sectionId: string,
): Promise<number> {
    return page.locator(`#${sectionId}`).evaluate((element) =>
        Math.round(element.getBoundingClientRect().top),
    );
}

/**
 * Wait until a Contact section is aligned beneath the compact public header.
 */
async function expectSectionAligned(
    page: Page,
    sectionId: string,
): Promise<void> {
    await expect
        .poll(
            async () => {
                const [headerEdge, currentSectionTop] =
                    await Promise.all([
                        headerBottom(page),
                        sectionTop(page, sectionId),
                    ]);

                return Math.abs(
                    currentSectionTop - headerEdge,
                );
            },
            {
                timeout: 1500,
            },
        )
        .toBeLessThanOrEqual(2);
}

test.describe("Contact page desktop scroll experience", () => {
    test.use({
        viewport: desktopViewport,
    });

    test("matches About section pacing and coordinated reveal behavior", async ({
        page,
    }) => {
        await page.goto("/contact");

        const root = page.locator("[data-contact-page]");

        const pager = page.locator(
            '[data-section-pager-context="contact"]',
        );

        await expect(root).toHaveAttribute(
            "data-contact-motion",
            "ready",
        );

        await expect(pager).toHaveAttribute(
            "data-section-pager-state",
            "ready",
        );

        await expect(pager).toHaveAttribute(
            "data-section-pager-snap-count",
            "0",
        );

        const heroBackground = await page
            .locator("#contact-hero")
            .evaluate(
                (element) =>
                    window.getComputedStyle(element).backgroundImage,
            );

        expect(heroBackground).not.toBe("none");
        expect(heroBackground).toContain("linear-gradient");

        await page.mouse.wheel(0, 160);

        await expectSectionAligned(
            page,
            "contact-message",
        );

        await expect(pager).toHaveAttribute(
            "data-section-pager-active",
            "contact-message",
        );

        await expect(pager).toHaveAttribute(
            "data-section-pager-snap-count",
            "1",
        );

        await expect(
            page.locator("#contact-message"),
        ).toHaveAttribute(
            "data-contact-animation-state",
            "complete",
        );

        const hiddenTargets = await page
            .locator(
                '#contact-message [data-gsap-reveal], '
                    + '#contact-message [data-gsap="panel"]',
            )
            .evaluateAll((elements) =>
                elements.filter((element) => {
                    const styles =
                        window.getComputedStyle(element);

                    return (
                        styles.opacity === "0"
                        || styles.visibility === "hidden"
                    );
                }).length,
            );

        expect(hiddenTargets).toBe(0);
    });

    test("keeps native scrolling on short desktop viewports", async ({
        page,
    }) => {
        await page.setViewportSize({
            width: 1280,
            height: 700,
        });

        await page.goto("/contact");

        const pager = page.locator(
            '[data-section-pager-context="contact"]',
        );

        await expect(pager).toHaveAttribute(
            "data-section-pager-state",
            "ready",
        );

        await page.mouse.wheel(0, 240);

        await expect
            .poll(() =>
                page.evaluate(() => window.scrollY),
            )
            .toBeGreaterThan(0);

        await expect(pager).toHaveAttribute(
            "data-section-pager-snap-count",
            "0",
        );
    });

    test("renders final readable states for reduced motion", async ({
        page,
    }) => {
        await page.emulateMedia({
            reducedMotion: "reduce",
        });

        await page.goto("/contact");

        const root = page.locator("[data-contact-page]");

        await expect(root).toHaveAttribute(
            "data-contact-motion",
            "reduced",
        );

        const hiddenTargets = await page
            .locator(
                '[data-contact-page] [data-gsap-reveal], '
                    + '[data-contact-page] [data-gsap="panel"]',
            )
            .evaluateAll((elements) =>
                elements.filter((element) => {
                    const styles =
                        window.getComputedStyle(element);

                    return (
                        styles.opacity === "0"
                        || styles.visibility === "hidden"
                    );
                }).length,
            );

        expect(hiddenTargets).toBe(0);
    });
});
