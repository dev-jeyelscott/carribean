import {
    expect,
    test,
} from "@playwright/test";

test.describe(
    "Gallery experience",
    () => {
        test(
            "renders one section and supports fullscreen keyboard navigation",
            async ({ page }) => {
                await page.goto("/gallery");

                const gallery = page.locator(
                    "[data-gallery-page]",
                );

                await expect(gallery)
                    .toHaveAttribute(
                        "data-gallery-motion-state",
                        "ready",
                    );

                await expect(
                    gallery.locator("section"),
                ).toHaveCount(1);

                const openers = page.locator(
                    "[data-gallery-open]",
                );

                await expect(
                    openers.first(),
                ).toBeVisible();

                const openerCount =
                    await openers.count();

                test.skip(
                    openerCount < 2,
                    "At least two seeded images are required.",
                );

                const firstOpener =
                    openers.first();

                await firstOpener.click();

                const dialog = page.locator(
                    "[data-gallery-dialog]",
                );

                const dialogImage = page.locator(
                    "[data-gallery-dialog-image]",
                );

                await expect(dialog)
                    .toHaveAttribute(
                        "open",
                        "",
                    );

                await expect(dialogImage)
                    .toHaveAttribute(
                        "src",
                        /.+/,
                    );

                const firstSource =
                    await dialogImage.getAttribute(
                        "src",
                    );

                await page
                    .getByRole(
                        "button",
                        {
                            name: "View next image",
                        },
                    )
                    .click();

                await expect
                    .poll(
                        async () =>
                            dialogImage.getAttribute(
                                "src",
                            ),
                    )
                    .not
                    .toBe(firstSource);

                await page.keyboard.press(
                    "ArrowLeft",
                );

                await expect
                    .poll(
                        async () =>
                            dialogImage.getAttribute(
                                "src",
                            ),
                    )
                    .toBe(firstSource);

                await page.keyboard.press(
                    "Escape",
                );

                await expect(dialog)
                    .not
                    .toHaveAttribute(
                        "open",
                        "",
                    );

                await expect(firstOpener)
                    .toBeFocused();
            },
        );

        test(
            "keeps the fullscreen viewer usable with reduced motion",
            async ({ page }) => {
                await page.emulateMedia({
                    reducedMotion: "reduce",
                });

                await page.goto("/gallery");

                const firstOpener = page
                    .locator(
                        "[data-gallery-open]",
                    )
                    .first();

                await firstOpener.click();

                const dialog = page.locator(
                    "[data-gallery-dialog]",
                );

                await expect(dialog)
                    .toHaveAttribute(
                        "open",
                        "",
                    );

                await page.keyboard.press(
                    "Escape",
                );

                await expect(dialog)
                    .not
                    .toHaveAttribute(
                        "open",
                        "",
                    );
            },
        );
    },
);
