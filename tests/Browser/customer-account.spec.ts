import {
    expect,
    test,
} from '@playwright/test';

test(
    'customer can register and open order history',
    async ({ page }) => {
        await page.goto('/register');

        await page
            .getByLabel('Full name')
            .fill('New Browser Customer');

        await page
            .getByLabel('Email address')
            .fill(
                'new.browser.customer@example.com',
            );

        await page
            .getByLabel('Phone number')
            .fill('555-0110');

        await page
            .getByLabel(
                'Password',
                {
                    exact: true,
                },
            )
            .fill('password');

        await page
            .getByLabel('Confirm password')
            .fill('password');

        await page
            .getByRole(
                'button',
                {
                    name: 'Create account',
                },
            )
            .click();

        await expect(page).not.toHaveURL(
            /\/register$/,
        );

        await page.goto(
            '/account/orders',
        );

        await expect(
            page.getByRole(
                'heading',
                {
                    name: 'Your orders',
                },
            ),
        ).toBeVisible();

        await expect(
            page.getByText(
                'Your first order is still ahead.',
            ),
        ).toBeVisible();
    },
);
