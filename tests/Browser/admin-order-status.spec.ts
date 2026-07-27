import {
    expect,
    test,
} from '@playwright/test';

test(
    'administrator confirms an order and customer sees the update',
    async ({ browser }) => {
        const adminContext =
            await browser.newContext();

        const adminPage =
            await adminContext.newPage();

        await adminPage.goto(
            '/admin/login',
        );

        await adminPage
            .locator(
                'input[name="email"]',
            )
            .fill(
                'browser.admin@example.com',
            );

        await adminPage
            .locator(
                'input[name="password"]',
            )
            .fill('password');

        await adminPage
            .getByRole(
                'button',
                {
                    name: /sign in/i,
                },
            )
            .click();

        await adminPage.goto(
            '/admin/orders',
        );

        await adminPage
            .getByText(
                'CC-BROWSER-0001',
            )
            .click();

        await expect(
            adminPage.getByText(
                'Pending Confirmation',
            ),
        ).toBeVisible();

        await adminPage
            .getByRole(
                'button',
                {
                    name: 'Confirm Order',
                },
            )
            .click();

        await adminPage
            .getByRole(
                'button',
                {
                    name: 'Confirm Order',
                },
            )
            .last()
            .click();

        await expect(
            adminPage.getByText(
                'Order marked Confirmed',
            ),
        ).toBeVisible();

        await adminContext.close();

        const customerContext =
            await browser.newContext();

        const customerPage =
            await customerContext.newPage();

        await customerPage.goto(
            '/login',
        );

        await customerPage
            .getByLabel('Email address')
            .fill(
                'browser.customer@example.com',
            );

        await customerPage
            .getByLabel('Password')
            .fill('password');

        await customerPage
            .getByRole(
                'button',
                {
                    name: 'Log in',
                },
            )
            .click();

        await customerPage.goto(
            '/account/orders',
        );

        await expect(
            customerPage.getByText(
                'CC-BROWSER-0001',
            ),
        ).toBeVisible();

        await expect(
            customerPage.getByText(
                'Confirmed',
                {
                    exact: true,
                },
            ),
        ).toBeVisible();

        await customerPage
            .getByRole(
                'link',
                {
                    name: 'View order',
                },
            )
            .click();

        await expect(
            customerPage.getByText(
                'Confirmed',
                {
                    exact: true,
                },
            ),
        ).toBeVisible();

        await customerContext.close();
    },
);
