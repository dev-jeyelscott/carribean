import { expect, test } from "@playwright/test";

test("administrator confirms an order and customer sees the update", async ({
    browser,
    baseURL,
}) => {
    const adminContext = await browser.newContext({
        baseURL,
    });

    const adminPage = await adminContext.newPage();

    await adminPage.goto("/admin/login");

    const adminEmailInput = adminPage.getByRole("textbox", {
        name: /email/i,
    });

    await expect(adminEmailInput).toBeVisible();

    await adminEmailInput.fill("browser.admin@example.com");

    const adminPasswordInput = adminPage.getByRole("textbox", {
        name: /^Password\s*\*?$/i,
    });

    await expect(adminPasswordInput).toBeVisible();

    await adminPasswordInput.fill("password");

    await adminPage
        .getByRole("button", {
            name: /sign in/i,
        })
        .click();

    await expect(adminPage).not.toHaveURL(/\/admin\/login$/);

    await adminPage.goto("/admin/orders");

    await expect(adminPage).toHaveURL(/\/admin\/orders$/);

    await adminPage
        .getByText("CC-BROWSER-0001", {
            exact: true,
        })
        .click();

    await expect(adminPage).toHaveURL(/\/admin\/orders\/[^/]+$/);

    /*
     * Scope status assertions to the authoritative order-overview section.
     *
     * The status-history section intentionally retains previous status labels,
     * so an unrestricted text locator could report a false positive.
     */
    const orderOverview = adminPage.locator(
        '[id="infolist.order-overview::section"]',
    );

    await expect(orderOverview).toBeVisible();

    await expect(
        orderOverview.getByText("Pending Confirmation", {
            exact: true,
        }),
    ).toBeVisible();

    /*
     * The action is available only while the order can transition from
     * Pending Confirmation to Confirmed.
     */
    const confirmOrderAction = adminPage.getByRole("button", {
        name: "Confirm Order",
        exact: true,
    });

    await expect(confirmOrderAction).toBeVisible();

    await confirmOrderAction.click();

    /*
     * Target the mounted Filament action modal by its application action key.
     */
    const confirmationModal = adminPage.locator(
        '[wire\\:key$="actions.transitionToConfirmed.modal"]',
    );

    const confirmationButton = confirmationModal.getByRole("button", {
        name: "Confirm Order",
        exact: true,
    });

    await expect(confirmationButton).toBeVisible();

    await confirmationButton.click();

    await expect(
        adminPage.getByText("Order marked Confirmed", {
            exact: true,
        }),
    ).toBeVisible();

    await expect(
        orderOverview.getByText("Confirmed", {
            exact: true,
        }),
    ).toBeVisible();

    await adminContext.close();

    const customerContext = await browser.newContext({
        baseURL,
    });

    const customerPage = await customerContext.newPage();

    await customerPage.goto("/login");

    await customerPage
        .getByLabel("Email address")
        .fill("browser.customer@example.com");

    await customerPage
        .getByLabel("Password", {
            exact: true,
        })
        .fill("password");

    await customerPage
        .getByRole("button", {
            name: "Log in",
        })
        .click();

    await expect(customerPage).not.toHaveURL(/\/login$/);

    await customerPage.goto("/account/orders");

    const customerOrderCard = customerPage.locator("article").filter({
        hasText: "CC-BROWSER-0001",
    });

    await expect(customerOrderCard).toBeVisible();

    await expect(
        customerOrderCard.getByText("Confirmed", {
            exact: true,
        }),
    ).toBeVisible();

    await customerOrderCard
        .getByRole("link", {
            name: "View order",
            exact: true,
        })
        .click();

    await expect(
        customerPage.getByRole("heading", {
            name: "Confirmed",
            exact: true,
        }),
    ).toBeVisible();

    await customerContext.close();
});
