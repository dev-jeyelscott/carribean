import {
    expect,
    test,
} from "@playwright/test";

test.describe("public page visual contracts", () => {
    test("public pages use the shared transparent navigation", async ({
        page,
    }) => {
        const paths = [
            "/menu",
            "/blog",
            "/contact",
            "/cart",
        ];

        for (const path of paths) {
            await page.goto(path);

            await expect(page.locator("body")).toHaveAttribute(
                "data-public-header-transparent",
                "true",
            );

            await expect(
                page.locator(
                    "[data-public-header-shell] > header",
                ),
            ).toBeVisible();
        }
    });

    test("journal listing and article use full-screen heroes", async ({
        page,
    }) => {
        await page.goto("/blog");

        const listingHero = page.locator(
            "[data-public-fullscreen-hero]",
        );

        await expect(listingHero).toBeVisible();

        const listingHeroHeight = await listingHero.evaluate(
            (element) => element.getBoundingClientRect().height,
        );

        const viewportHeight = await page.evaluate(
            () => window.innerHeight,
        );

        expect(listingHeroHeight).toBeGreaterThanOrEqual(
            viewportHeight - 2,
        );

        await page.goto(
            "/blog/welcome-to-coast-and-cay",
        );

        const articleHero = page.locator(
            "[data-public-fullscreen-hero]",
        );

        await expect(articleHero).toBeVisible();
        await expect(
            page.getByRole("heading", {
                level: 1,
                name: "What We Mean by a Caribbean Table",
            }),
        ).toBeVisible();

        const articleHeroHeight = await articleHero.evaluate(
            (element) => element.getBoundingClientRect().height,
        );

        expect(articleHeroHeight).toBeGreaterThanOrEqual(
            viewportHeight - 2,
        );
    });

    test("menu uses the shared island atmosphere", async ({
        page,
    }) => {
        await page.goto("/menu");

        const menuPage = page.locator("[data-menu-page]");

        await expect(menuPage).toBeVisible();

        const backgroundImage = await menuPage.evaluate(
            (element) => getComputedStyle(element).backgroundImage,
        );

        expect(backgroundImage).toContain("linear-gradient");
    });

    test("authenticated customers see the redesigned order list", async ({
        page,
    }) => {
        await page.goto("/login");

        await page
            .getByLabel("Email address")
            .fill("browser.customer@example.com");

        await page
            .getByLabel("Password")
            .fill("password");

        await page
            .locator('[data-test="login-button"]')
            .click();

        await page.goto("/account/orders");

        await expect(
            page.getByRole("heading", {
                level: 1,
                name: /Your orders/i,
            }),
        ).toBeVisible();

        await expect(
            page.getByText("CC-BROWSER-0001"),
        ).toBeVisible();

        await expect(
            page.locator(".order-history-card"),
        ).toHaveCount(1);
    });
});
