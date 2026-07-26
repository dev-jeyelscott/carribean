<?php

use App\Actions\Orders\PlaceOrder;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\Cart\SessionCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Create one pending pickup order for transition tests.
 */
function createPendingTransitionOrder()
{
    foreach ([
        'accepting_online_orders' => '1',
        'tax_rate_basis_points' => '0',
        'cash_at_pickup_enabled' => '1',
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
        'name' => 'Transitions',
        'slug' => 'transition-category',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $item = MenuItem::query()->create([
        'menu_category_id' => $category->id,
        'name' => 'Rice and Peas',
        'slug' => 'transition-rice-and-peas',
        'price_cents' => 1_000,
        'sort_order' => 1,
        'is_visible' => true,
        'is_featured' => false,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    app(SessionCart::class)->add(
        $item->id,
        [],
        1,
    );

    return app(PlaceOrder::class)->execute(
        app(SessionCart::class),
        null,
        [
            'name' => 'Jamie Lee',
            'email' => 'jamie@example.com',
            'phone' => '555-0103',
        ],
        FulfillmentMethod::Pickup,
        PaymentMethod::CashAtPickup,
        null,
        null,
        (string) Str::uuid(),
    );
}

beforeEach(function (): void {
    session()->forget('shopping_cart');
});

test(
    'pickup order follows the approved lifecycle',
    function (): void {
        $order = createPendingTransitionOrder();

        $transition = app(
            TransitionOrderStatus::class,
        );

        $order = $transition->execute(
            $order,
            OrderStatus::Confirmed,
        );

        $order = $transition->execute(
            $order,
            OrderStatus::Preparing,
        );

        $order = $transition->execute(
            $order,
            OrderStatus::ReadyForPickup,
        );

        $order = $transition->execute(
            $order,
            OrderStatus::PickedUp,
        );

        $order = $transition->execute(
            $order,
            OrderStatus::Completed,
        );

        expect($order)
            ->status->toBe(
                OrderStatus::Completed,
            )
            ->picked_up_at->not->toBeNull()
            ->completed_at->not->toBeNull();

        expect(
            $order->statusHistories,
        )->toHaveCount(6);
    },
);

test(
    'invalid status jumps are rejected',
    function (): void {
        $order = createPendingTransitionOrder();

        expect(
            fn () => app(
                TransitionOrderStatus::class,
            )->execute(
                $order,
                OrderStatus::Delivered,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            $order->fresh()?->status,
        )->toBe(
            OrderStatus::PendingConfirmation,
        );
    },
);
