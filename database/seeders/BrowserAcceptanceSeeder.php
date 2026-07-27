<?php

namespace Database\Seeders;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BrowserAcceptanceSeeder extends Seeder
{
    /**
     * Seed deterministic application data used by Playwright acceptance tests.
     */
    public function run(): void
    {
        $this->call(
            DatabaseSeeder::class,
        );

        $this->seedOrderingSettings();

        $customer = $this->seedCustomer();

        $this->seedPendingCustomerOrder(
            $customer,
        );
    }

    /**
     * Enable the minimal ordering configuration required by browser tests.
     */
    private function seedOrderingSettings(): void
    {
        $settings = [
            [
                'key' => 'accepting_online_orders',
                'value' => '1',
                'group' => 'ordering',
            ],
            [
                'key' => 'cash_at_pickup_enabled',
                'value' => '1',
                'group' => 'ordering',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::query()->updateOrCreate(
                [
                    'key' => $setting['key'],
                ],
                $setting,
            );
        }

        SiteSetting::forgetCachedValues();
    }

    /**
     * Create the registered customer used by order-tracking tests.
     */
    private function seedCustomer(): User
    {
        return User::query()->updateOrCreate(
            [
                'email' => 'browser.customer@example.com',
            ],
            [
                'name' => 'Browser Customer',
                'phone' => '555-0199',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Create one pending pickup order for the admin workflow test.
     */
    private function seedPendingCustomerOrder(
        User $customer,
    ): void {
        $menuItem = MenuItem::query()
            ->where(
                'slug',
                'island-jerk-chicken',
            )
            ->firstOrFail();

        $order = Order::query()->create([
            'public_id' => (string) Str::ulid(),
            'order_number' => 'CC-BROWSER-0001',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => OrderStatus::PendingConfirmation,
            'payment_status' => PaymentStatus::Pending,
            'fulfillment_method' => FulfillmentMethod::Pickup,
            'payment_method' => PaymentMethod::CashAtPickup,
            'currency' => 'USD',
            'subtotal_cents' => 2_400,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'delivery_cents' => 0,
            'grand_total_cents' => 2_400,
            'tax_rate_basis_points' => 0,
            'coupon_code' => null,
            'coupon_snapshot' => null,
            'customer_note' => 'Browser acceptance order.',
            'internal_note' => null,
            'checkout_idempotency_token' => (string) Str::uuid(),
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'description' => $menuItem->description,
            'base_unit_price_cents' => 2_400,
            'quantity' => 1,
            'selected_options' => [
                [
                    'group_name' => 'Spice Level',
                    'name' => 'Mild',
                    'additional_price_cents' => 0,
                ],
                [
                    'group_name' => 'Choose a Side',
                    'name' => 'Rice and Peas',
                    'additional_price_cents' => 0,
                ],
            ],
            'option_total_cents' => 0,
            'unit_price_cents' => 2_400,
            'line_total_cents' => 2_400,
        ]);

        $order->payments()->create([
            'provider' => 'cash',
            'payment_method' => PaymentMethod::CashAtPickup,
            'status' => PaymentStatus::Pending,
            'amount_cents' => 2_400,
            'currency' => 'USD',
        ]);

        $order->statusHistories()->create([
            'previous_status' => null,
            'new_status' => OrderStatus::PendingConfirmation,
            'public_note' => 'Order received.',
            'internal_note' => null,
        ]);
    }
}
