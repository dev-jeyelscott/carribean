import {
    expect,
    test,
} from '@playwright/test';

test(
    'guest can configure an item and place a pickup cash order',
    async ({ page }) => {
        await page.goto(
            '/menu/island-jerk-chicken',
        );

        await expect(
            page.getByRole(
                'heading',
                {
                    name: 'Island Jerk Chicken',
                },
            ),
        ).toBeVisible();

        await page
            .getByLabel('Mild')
            .check();

        await page
            .getByLabel('Rice and Peas')
            .check();

        await page
            .getByRole(
                'button',
                {
                    name: 'Add to Cart',
                },
            )
            .click();

        await page.goto('/cart');

        await expect(
            page.getByRole(
                'heading',
                {
                    name: 'Shopping Cart',
                },
            ),
        ).toBeVisible();

        await expect(
            page.getByText(
                'Island Jerk Chicken',
            ),
        ).toBeVisible();

        await page
            .getByLabel('Fulfillment')
            .selectOption('pickup');

        await page
            .getByRole(
                'button',
                {
                    name: 'Update Fulfillment',
                },
            )
            .click();

        await page
            .getByRole(
                'link',
                {
                    name: 'Proceed to Checkout',
                },
            )
            .click();

        await page
            .getByLabel('Full name')
            .fill('Guest Acceptance');

        await page
            .getByLabel('Email')
            .fill(
                'guest.acceptance@example.com',
            );

        await page
            .getByLabel('Phone')
            .fill('555-0108');

        await page
            .getByLabel('Cash at pickup')
            .check();

        await page
            .getByRole(
                'button',
                {
                    name: 'Place Order',
                },
            )
            .click();

        await expect(page).toHaveURL(
            /\/checkout\/success\//,
        );

        await expect(
            page.getByRole(
                'heading',
                {
                    name: 'Thank You',
                },
            ),
        ).toBeVisible();

        await expect(
            page.getByText(
                'Pending Confirmation',
            ),
        ).toBeVisible();

        await expect(
            page.getByRole(
                'link',
                {
                    name: 'Track This Order',
                },
            ),
        ).toBeVisible();
    },
);
