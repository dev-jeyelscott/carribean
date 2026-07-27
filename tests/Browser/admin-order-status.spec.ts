import {
    expect,
    test,
} from '@playwright/test';

test(
    'administrator confirms an order and customer sees the update',
    async ({
        browser,
        baseURL,
    }) => {
        const adminContext =
            await browser.newContext({
                baseURL,
            });

        const adminPage =
            await adminContext.newPage();

        await adminPage.goto(
            '/admin/login',
        );

        const adminEmailInput =
            adminPage.getByRole(
                'textbox',
                {
                    name: /email/i,
                },
            );

        await expect(
            adminEmailInput,
        ).toBeVisible();

        await adminEmailInput.fill(
            'browser.admin@example.com',
        );

        const adminPasswordInput =
            adminPage.getByLabel(
                /password/i,
            );

        await expect(
            adminPasswordInput,
        ).toBeVisible();

        await adminPasswordInput.fill(
            'password',
        );

        await adminPage
            .getByRole(
                'button',
                {
                    name: /sign in/i,
                },
            )
            .click();

        await expect(
            adminPage,
        ).not.toHaveURL(
            /\/admin\/login$/,
        );

        await adminPage.goto(
            '/admin/orders',
        );

        await expect(
            adminPage,
        ).toHaveURL(
            /\/admin\/orders$/,
        );

        await adminPage
            .getByText(
                'CC-BROWSER-0001',
                {
                    exact: true,
                },
            )
            .click();

        await expect(
            adminPage.getByText(
                'Pending Confirmation',
                {
                    exact: true,
                },
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

        const confirmationDialog =
            adminPage.getByRole(
                'dialog',
            );

        await expect(
            confirmationDialog,
        ).toBeVisible();

        await confirmationDialog
            .getByRole(
                'button',
                {
                    name: 'Confirm Order',
                },
            )
            .click();

        await expect(
            adminPage.getByText(
                'Order marked Confirmed',
            ),
        ).toBeVisible();

        await adminContext.close();

        const customerContext =
            await browser.newContext({
                baseURL,
            });

        const customerPage =
            await customerContext.newPage();

        await customerPage.goto(
            '/login',
        );

        await customerPage
            .getByLabel(
                'Email address',
            )
            .fill(
                'browser.customer@example.com',
            );

        await customerPage
            .getByLabel(
                'Password',
                {
                    exact: true,
                },
            )
            .fill('password');

        await customerPage
            .getByRole(
                'button',
                {
                    name: 'Log in',
                },
            )
            .click();

        await expect(
            customerPage,
        ).not.toHaveURL(
            /\/login$/,
        );

        await customerPage.goto(
            '/account/orders',
        );

        await expect(
            customerPage.getByText(
                'CC-BROWSER-0001',
                {
                    exact: true,
                },
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
