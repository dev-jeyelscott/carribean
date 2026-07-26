<?php

use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use App\Livewire\Checkout\CheckoutPage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Services\StripeCheckoutService;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Stripe\Checkout\Session;

uses(RefreshDatabase::class);

/**
 * Prepare checkout settings and one cart item.
 */
function prepareCheckoutPage(): void
{
    foreach ([
        'accepting_online_orders' => '1',
        'accepted_delivery_zip_codes' => '90001',
        'delivery_fee_cents' => '500',
        'delivery_minimum_cents' => '1000',
        'tax_rate_basis_points' => '825',
        'cash_at_pickup_enabled' => '1',
        'cash_on_delivery_enabled' => '1',
    ] as $key => $value) {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => 'ordering',
            ],
        );
    }

    $category = MenuCategory::query()->create([
        'name' => 'Checkout',
        'slug' => 'checkout-page-category',
        'description' => null,
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $menuItem = MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Curry Chicken',
        'slug' => 'checkout-curry-chicken',
        'description' => null,
        'price_cents' => 2_000,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    app(SessionCart::class)->add(
        $menuItem->id,
        [],
        1,
    );
}

beforeEach(function (): void {
    session()->forget('shopping_cart');
    session()->forget(
        'checkout.idempotency_token',
    );

    prepareCheckoutPage();
});

test(
    'guest can complete pickup checkout through livewire',
    function (): void {
        Livewire::test(CheckoutPage::class)
            ->assertSet(
                'paymentMethod',
                PaymentMethod::CashAtPickup->value,
            )
            ->set('name', 'Taylor Brown')
            ->set(
                'email',
                'taylor@example.com',
            )
            ->set('phone', '555-0102')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        expect(Order::query()->count())
            ->toBe(1);

        expect(Order::query()->first())
            ->fulfillment_method->toBe(
                FulfillmentMethod::Pickup,
            )
            ->payment_method->toBe(
                PaymentMethod::CashAtPickup,
            );
    },
);

test(
    'checkout success requires a valid signed url',
    function (): void {
        Livewire::test(CheckoutPage::class)
            ->set('name', 'Taylor Brown')
            ->set(
                'email',
                'taylor@example.com',
            )
            ->set('phone', '555-0102')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->firstOrFail();

        $signedUrl =
            URL::temporarySignedRoute(
                'checkout.success',
                now()->addHour(),
                [
                    'order' => $order,
                ],
            );

        $this->get($signedUrl)
            ->assertOk()
            ->assertSee(
                $order->order_number,
            );

        $this->get(
            route(
                'checkout.success',
                $order,
            ),
        )->assertForbidden();
    },
);

test(
    'guest can start stripe hosted checkout',
    function (): void {
        config([
            'services.stripe.secret' => 'sk_test_example',
        ]);

        $checkoutUrl =
            'https://checkout.stripe.com/c/pay/cs_test_phase_seven';

        $checkoutSession =
            Session::constructFrom([
                'id' => 'cs_test_phase_seven',
                'status' => 'open',
                'url' => $checkoutUrl,
            ]);

        $stripeCheckout =
            Mockery::mock(
                StripeCheckoutService::class,
            );

        $stripeCheckout
            ->shouldReceive(
                'createCheckoutSession',
            )
            ->once()
            ->andReturn(
                $checkoutSession,
            );

        app()->instance(
            StripeCheckoutService::class,
            $stripeCheckout,
        );

        Livewire::test(CheckoutPage::class)
            ->set(
                'paymentMethod',
                PaymentMethod::Stripe->value,
            )
            ->set(
                'name',
                'Stripe Customer',
            )
            ->set(
                'email',
                'stripe@example.com',
            )
            ->set(
                'phone',
                '555-0110',
            )
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect(
                $checkoutUrl,
            );

        $order =
            Order::query()
                ->firstOrFail();

        expect($order->payment_method)
            ->toBe(
                PaymentMethod::Stripe,
            );

        expect(
            $order->payments()
                ->firstOrFail()
                ->provider,
        )->toBe('stripe');
    },
);
