import {
    expect,
    test,
    type Page,
    type Response,
} from '@playwright/test';

/**
 * Determine whether the response belongs to a Livewire component update.
 *
 * Livewire 4 may fingerprint its update endpoint, producing URLs such as:
 * /livewire-5882f8c6/update
 */
function isLivewireUpdateResponse(
    response: Response,
): boolean {
    const url = new URL(
        response.url(),
    );

    return response
        .request()
        .method() === 'POST'
        && /^\/livewire(?:-[^/]+)?\/update$/.test(
            url.pathname,
        );
}

/**
 * Execute a UI action and wait for its Livewire request to complete.
 */
async function runLivewireAction(
    page: Page,
    action: () => Promise<void>,
): Promise<Response> {
    const [
        response,
    ] = await Promise.all([
        page.waitForResponse(
            isLivewireUpdateResponse,
        ),
        action(),
    ]);

    expect(
        response.ok(),
    ).toBeTruthy();

    return response;
}

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

        await runLivewireAction(
            page,
            async (): Promise<void> => {
                await page
                    .getByRole(
                        'button',
                        {
                            name: 'Add to Cart',
                        },
                    )
                    .click();
            },
        );

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
                {
                    exact: true,
                },
            ),
        ).toBeVisible();

        await page
            .getByLabel('Fulfillment')
            .selectOption('pickup');

        await runLivewireAction(
            page,
            async (): Promise<void> => {
                await page
                    .getByRole(
                        'button',
                        {
                            name: 'Update Fulfillment',
                        },
                    )
                    .click();
            },
        );

        await page
            .getByRole(
                'link',
                {
                    name: 'Proceed to Checkout',
                },
            )
            .click();

        await expect(
            page,
        ).toHaveURL(
            /\/checkout$/,
        );

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

        await Promise.all([
            page.waitForURL(
                /\/checkout\/success\//,
            ),

            page
                .getByRole(
                    'button',
                    {
                        name: 'Place Order',
                    },
                )
                .click(),
        ]);

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
                {
                    exact: true,
                },
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
