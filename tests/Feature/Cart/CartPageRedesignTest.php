<?php

use App\Enums\FulfillmentMethod;
use App\Livewire\Cart\CartPage;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use Database\Seeders\MenuSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(MenuSeeder::class);

    $settings = [
        'accepting_online_orders' => '1',
        'online_orders_closed_message' => 'Online ordering is temporarily unavailable.',
        'accepted_delivery_zip_codes' => '90210, 90001',
        'delivery_fee_cents' => '700',
        'delivery_minimum_cents' => '0',
        'tax_rate_basis_points' => '0',
    ];

    foreach ($settings as $key => $value) {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => 'ordering',
            ],
        );
    }

    SiteSetting::forgetCachedValues();
});

/**
 * Add one valid configured jerk-chicken line to the current test session.
 */
function addConfiguredCartLine(int $quantity = 1): string
{
    $menuItem = MenuItem::query()
        ->where('slug', 'island-jerk-chicken')
        ->with('optionGroups.options')
        ->firstOrFail();

    $optionIds = [];

    foreach ($menuItem->optionGroups as $group) {
        if ($group->minimum_selections < 1) {
            continue;
        }

        $optionIds[] = $group->options->firstOrFail()->id;
    }

    return app(SessionCart::class)->add(
        $menuItem->id,
        $optionIds,
        $quantity,
    );
}

it('increments and decrements a cart line through the server stepper', function (): void {
    $lineKey = addConfiguredCartLine();

    Livewire::test(CartPage::class)
        ->assertSet('quantities.'.$lineKey, 1)
        ->call('incrementQuantity', $lineKey)
        ->assertHasNoErrors()
        ->assertSet('quantities.'.$lineKey, 2)
        ->call('decrementQuantity', $lineKey)
        ->assertHasNoErrors()
        ->assertSet('quantities.'.$lineKey, 1);

    expect(
        app(SessionCart::class)->items()[$lineKey]['quantity'],
    )->toBe(1);
});

it('rejects quantity changes beyond the configured maximum', function (): void {
    $lineKey = addConfiguredCartLine(
        SessionCart::MAX_QUANTITY,
    );

    Livewire::test(CartPage::class)
        ->call('incrementQuantity', $lineKey)
        ->assertHasErrors([
            'quantities.'.$lineKey,
        ])
        ->assertSet(
            'quantities.'.$lineKey,
            SessionCart::MAX_QUANTITY,
        );

    expect(
        app(SessionCart::class)->items()[$lineKey]['quantity'],
    )->toBe(SessionCart::MAX_QUANTITY);
});

it('retains an invalid delivery zip and persists a valid zip', function (): void {
    addConfiguredCartLine();

    Livewire::test(CartPage::class)
        ->call(
            'selectFulfillment',
            FulfillmentMethod::Delivery->value,
        )
        ->assertSet(
            'fulfillmentMethod',
            FulfillmentMethod::Delivery->value,
        )
        ->set('deliveryZip', '99999')
        ->call('saveFulfillment')
        ->assertHasErrors(['deliveryZip'])
        ->assertSet('deliveryZip', '99999')
        ->set('deliveryZip', '90210')
        ->call('saveFulfillment')
        ->assertHasNoErrors()
        ->assertSet('deliveryZip', '90210');

    $sessionCart = app(SessionCart::class);

    expect($sessionCart->fulfillmentMethod())
        ->toBe(FulfillmentMethod::Delivery)
        ->and($sessionCart->deliveryZip())
        ->toBe('90210');
});

it('keeps cart contents visible while online ordering is closed', function (): void {
    addConfiguredCartLine();

    SiteSetting::query()->updateOrCreate(
        ['key' => 'accepting_online_orders'],
        [
            'value' => '0',
            'group' => 'ordering',
        ],
    );

    SiteSetting::forgetCachedValues();

    Livewire::test(CartPage::class)
        ->assertSee('Island Jerk Chicken')
        ->assertSee('Online ordering is paused')
        ->assertSee('Continue to Checkout')
        ->assertDontSeeHtml('data-cart-checkout');
});
