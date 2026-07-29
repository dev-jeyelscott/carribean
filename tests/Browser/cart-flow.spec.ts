import { expect, test } from "@playwright/test";

/**
 * Verify that a customer can configure a menu item, persist it to the
 * server-side session cart, update its quantity, and validate fulfillment.
 */
test("customer reviews and updates the redesigned cart", async ({ page }) => {
    await page.goto("/menu/island-jerk-chicken");

    /*
     * Configure all required menu-item option groups before submitting the
     * Livewire add-to-cart form.
     */
    await page.getByRole("radio", { name: /Mild/ }).check();
    await page.getByRole("radio", { name: /Rice and Peas/ }).check();

    const addToCartButton = page.getByRole("button", {
        name: "Add to Cart",
        exact: true,
    });

    await expect(addToCartButton).toBeVisible();
    await expect(addToCartButton).toBeEnabled();

    await addToCartButton.click();

    /*
     * The success notification is dispatched only after SessionCart::add()
     * completes. Waiting for this user-visible state prevents navigation from
     * cancelling the in-flight Livewire request.
     */
    const addedNotification = page.getByRole("status").filter({
        hasText: "Island Jerk Chicken was added to your cart.",
    });

    await expect(addedNotification).toBeVisible({
        timeout: 10_000,
    });

    /*
     * Verify the independent navbar cart-count component also received the
     * cart-updated event before leaving the item-detail page.
     */
    await expect(
        page.locator('[aria-label="1 item in cart"]:visible'),
    ).toHaveText("1");

    /*
     * Follow the actual public cart link rather than forcing a direct
     * navigation. This more accurately represents the customer flow.
     */
    await page
        .getByRole("link", {
            name: "View shopping cart",
            exact: true,
        })
        .click();

    await expect(page).toHaveURL(/\/cart$/);

    const cartLine = page.locator("[data-cart-line]").filter({
        hasText: "Island Jerk Chicken",
    });

    await expect(cartLine).toBeVisible({
        timeout: 10_000,
    });

    await expect(
        cartLine.locator("[data-cart-quantity]"),
    ).toHaveText("1");

    /*
     * Quantity changes remain server-authoritative through the cart Livewire
     * component.
     */
    await cartLine
        .getByRole("button", {
            name: "Increase quantity for Island Jerk Chicken",
            exact: true,
        })
        .click();

    await expect(
        cartLine.locator("[data-cart-quantity]"),
    ).toHaveText("2");

    await expect(
        page.locator('[data-summary-row="subtotal"]'),
    ).toContainText("$48.00");

    /*
     * Select local delivery through the native radio control. The California
     * settings seeder configures Santa Monica ZIP codes, a $7.50 delivery fee,
     * a $35.00 delivery minimum, and a 10.75% development tax rate.
     */
    await page
        .getByRole("radio", {
            name: /Local delivery/,
        })
        .check();

    await expect(
        page.getByRole("radio", {
            name: /Local delivery/,
        }),
    ).toBeChecked();

    const deliveryZip = page.getByLabel("Delivery ZIP code");

    await expect(deliveryZip).toBeVisible();

    /*
     * An unsupported ZIP must show server feedback without clearing the
     * customer's entered value or the rest of the cart.
     */
    await deliveryZip.fill("99999");

    await page
        .getByRole("button", {
            name: "Check ZIP",
            exact: true,
        })
        .click();

    await expect(
        page.getByText(
            "Local delivery is not available for this ZIP code.",
            {
                exact: true,
            },
        ),
    ).toBeVisible();

    await expect(deliveryZip).toHaveValue("99999");
    await expect(cartLine).toBeVisible();

    /*
     * Validate one delivery ZIP supplied by the California development
     * settings.
     */
    await deliveryZip.fill("90401");

    await page
        .getByRole("button", {
            name: "Check ZIP",
            exact: true,
        })
        .click();

    await expect(
        page.getByText(
            "Delivery is available for 90401.",
            {
                exact: true,
            },
        ),
    ).toBeVisible();

    await expect(
        page.locator('[data-summary-row="tax"]'),
    ).toContainText("10.75%");

    await expect(
        page.locator('[data-summary-row="tax"]'),
    ).toContainText("$5.16");

    await expect(
        page.locator('[data-summary-row="delivery"]'),
    ).toContainText("$7.50");

    await expect(
        page.locator('[data-summary-row="total"]'),
    ).toContainText("$60.66");

    await expect(
        page.locator("[data-cart-checkout]"),
    ).toBeVisible();

    /*
     * Switching back to pickup must remove the delivery fee and keep checkout
     * available.
     */
    await page
        .getByRole("radio", {
            name: /Pickup/,
        })
        .check();

    await expect(
        page.getByRole("radio", {
            name: /Pickup/,
        }),
    ).toBeChecked();

    await expect(deliveryZip).not.toBeVisible();

    await expect(
        page.locator('[data-summary-row="delivery"]'),
    ).toContainText("$0.00");

    await expect(
        page.locator('[data-summary-row="total"]'),
    ).toContainText("$53.16");

    await expect(
        page.locator("[data-cart-checkout]"),
    ).toBeVisible();
});
