import { expect, test } from "@playwright/test";

/**
 * Assert that a non-hero panel begins directly below the compact header and
 * that no part of the preceding panel remains visible below that header.
 */
async function expectExactPanelAlignment(
    page,
    currentPanelId: string,
    previousPanelId: string,
): Promise<void> {
    await expect
        .poll(async () => {
            return page.evaluate(
                ({ currentPanelId }) => {
                    const header = document.querySelector(
                        "[data-public-header-shell] > header",
                    );

                    const panel = document.getElementById(
                        currentPanelId,
                    );

                    if (
                        !(header instanceof HTMLElement)
                        || !(panel instanceof HTMLElement)
                    ) {
                        return Number.POSITIVE_INFINITY;
                    }

                    return Math.abs(
                        panel.getBoundingClientRect().top
                        - header.getBoundingClientRect().bottom,
                    );
                },
                {
                    currentPanelId,
                },
            );
        })
        .toBeLessThan(3);

    await expect
        .poll(async () => {
            return page.evaluate(
                ({ previousPanelId }) => {
                    const header = document.querySelector(
                        "[data-public-header-shell] > header",
                    );

                    const previousPanel =
                        document.getElementById(
                            previousPanelId,
                        );

                    if (
                        !(header instanceof HTMLElement)
                        || !(
                            previousPanel
                            instanceof HTMLElement
                        )
                    ) {
                        return Number.POSITIVE_INFINITY;
                    }

                    return Math.max(
                        0,
                        previousPanel
                            .getBoundingClientRect()
                            .bottom
                        - header
                            .getBoundingClientRect()
                            .bottom,
                    );
                },
                {
                    previousPanelId,
                },
            );
        })
        .toBeLessThan(3);

    await expect
        .poll(async () => {
            return page.evaluate(
                ({ currentPanelId }) => {
                    const header = document.querySelector(
                        "[data-public-header-shell] > header",
                    );

                    const panel = document.getElementById(
                        currentPanelId,
                    );

                    if (
                        !(header instanceof HTMLElement)
                        || !(panel instanceof HTMLElement)
                    ) {
                        return Number.POSITIVE_INFINITY;
                    }

                    const expectedHeight =
                        window.innerHeight
                        - header
                            .getBoundingClientRect()
                            .bottom;

                    return Math.abs(
                        panel.getBoundingClientRect().height
                        - expectedHeight,
                    );
                },
                {
                    currentPanelId,
                },
            );
        })
        .toBeLessThan(3);
}

test("gallery pager aligns full-screen panels and activates GSAP timelines", async ({
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
        '[href="#gallery-hero"]',
    );

    const signatureLink = pager.locator(
        '[href="#gallery-signature"]',
    );

    const collectionLink = pager.locator(
        '[href="#gallery-collection"]',
    );

    const invitationLink = pager.locator(
        '[href="#gallery-invitation"]',
    );

    await expect(heroLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await signatureLink.click();

    await expect(page).toHaveURL(
        /#gallery-signature$/,
    );

    await expect(signatureLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await expectExactPanelAlignment(
        page,
        "gallery-signature",
        "gallery-hero",
    );

    await expect(
        page.locator("#gallery-signature"),
    ).toHaveAttribute(
        "data-gallery-animation-state",
        "complete",
    );

    /*
     * Use a desktop wheel gesture to verify About-style adjacent navigation.
     */
    await page.evaluate(() => {
        window.dispatchEvent(
            new WheelEvent("wheel", {
                bubbles: true,
                cancelable: true,
                deltaY: 900,
            }),
        );
    });

    await expect(collectionLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await expectExactPanelAlignment(
        page,
        "gallery-collection",
        "gallery-signature",
    );

    await expect
        .poll(async () => {
            return Number(
                await pager.getAttribute(
                    "data-section-pager-snap-count",
                ),
            );
        })
        .toBeGreaterThan(0);

    await invitationLink.click();

    await expect(page).toHaveURL(
        /#gallery-invitation$/,
    );

    await expect(invitationLink).toHaveAttribute(
        "aria-current",
        "location",
    );

    await expectExactPanelAlignment(
        page,
        "gallery-invitation",
        "gallery-collection",
    );

    await expect(
        page.locator("#gallery-invitation"),
    ).toHaveAttribute(
        "data-gallery-animation-state",
        "complete",
    );

    await page.goBack();

    await expect(page).toHaveURL(
        /#gallery-signature$/,
    );

    await expect(signatureLink).toHaveAttribute(
        "aria-current",
        "location",
    );
});
