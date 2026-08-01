import {
    expect,
    test,
} from "@playwright/test";

test.use({
    reducedMotion: "no-preference",
    viewport: {
        width: 1440,
        height: 900,
    },
});

/**
 * Verify that the browser loads the Gallery chunk and creates ScrollTriggers.
 */
test(
    "gallery initializes and animates viewport content",
    async ({ page }) => {
        const runtimeErrors: string[] = [];

        page.on(
            "pageerror",
            (error) => {
                runtimeErrors.push(
                    error.message,
                );
            },
        );

        await page.goto("/gallery");

        const gallery = page.locator(
            "[data-gallery-page]",
        );

        await expect(gallery).toBeVisible();

        await expect(gallery).toHaveAttribute(
            "data-gallery-motion-state",
            "ready",
            {
                timeout: 10_000,
            },
        );

        await expect
            .poll(
                async () => {
                    const value =
                        await gallery.getAttribute(
                            "data-gallery-trigger-count",
                        );

                    return Number.parseInt(
                        value ?? "0",
                        10,
                    );
                },
                {
                    timeout: 10_000,
                },
            )
            .toBeGreaterThan(4);

        const signature = page.locator(
            "#gallery-signature",
        );

        await signature.scrollIntoViewIfNeeded();

        await expect(signature).toHaveAttribute(
            "data-gallery-animation-state",
            "complete",
            {
                timeout: 5_000,
            },
        );

        const firstGalleryItem = page
            .locator("[data-gallery-item]")
            .first();

        await expect(
            firstGalleryItem,
        ).toBeVisible();

        await firstGalleryItem
            .scrollIntoViewIfNeeded();

        await expect(
            firstGalleryItem,
        ).toHaveAttribute(
            "data-gallery-item-state",
            "complete",
            {
                timeout: 5_000,
            },
        );

        expect(runtimeErrors).toEqual([]);
    },
);

/**
 * Verify that reduced-motion users receive readable content without movement.
 */
test(
    "gallery respects reduced motion",
    async ({ page }) => {
        await page.emulateMedia({
            reducedMotion: "reduce",
        });

        await page.goto("/gallery");

        const gallery = page.locator(
            "[data-gallery-page]",
        );

        await expect(gallery).toHaveAttribute(
            "data-gallery-motion-state",
            "reduced",
            {
                timeout: 10_000,
            },
        );

        const firstReveal = page
            .locator("[data-gallery-reveal]")
            .first();

        await expect(firstReveal).toBeVisible();

        const styles = await firstReveal.evaluate(
            (element) => {
                const computed =
                    window.getComputedStyle(
                        element,
                    );

                return {
                    opacity: computed.opacity,
                    visibility:
                        computed.visibility,
                };
            },
        );

        expect(styles.opacity).toBe("1");
        expect(styles.visibility).not.toBe(
            "hidden",
        );
    },
);
