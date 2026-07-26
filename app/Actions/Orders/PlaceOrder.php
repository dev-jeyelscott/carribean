<?php

namespace App\Actions\Orders;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\StripeCheckoutService;
use App\Support\Cart\SessionCart;
use App\Support\Orders\OrderTotalsCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class PlaceOrder
{
    /**
     * Receive the existing authoritative order-total calculator.
     */
    public function __construct(
        private OrderTotalsCalculator $totalsCalculator,
    ) {}

    /**
     * Validate the current cart and create all order snapshots atomically.
     *
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>|null  $deliveryAddress
     */
    public function execute(
        SessionCart $sessionCart,
        ?User $user,
        array $customer,
        FulfillmentMethod $fulfillmentMethod,
        PaymentMethod $paymentMethod,
        ?array $deliveryAddress,
        ?string $customerNote,
        string $idempotencyToken,
    ): Order {
        $validatedCustomer = $this->validateCustomer(
            $customer,
        );

        $validatedAddress =
            $fulfillmentMethod
                === FulfillmentMethod::Delivery
            ? $this->validateDeliveryAddress(
                $deliveryAddress,
            )
            : null;

        $normalizedToken =
            $this->validateIdempotencyToken(
                $idempotencyToken,
            );

        $this->assertPaymentMethodAvailable(
            $fulfillmentMethod,
            $paymentMethod,
        );

        $existingOrder = Order::query()
            ->where(
                'checkout_idempotency_token',
                $normalizedToken,
            )
            ->first();

        if ($existingOrder instanceof Order) {
            $sessionCart->clear();

            return $this->loadOrder($existingOrder);
        }

        $order = DB::transaction(
            function () use (
                $sessionCart,
                $user,
                $validatedCustomer,
                $fulfillmentMethod,
                $paymentMethod,
                $validatedAddress,
                $customerNote,
                $normalizedToken,
            ): Order {
                $existingOrder = Order::query()
                    ->where(
                        'checkout_idempotency_token',
                        $normalizedToken,
                    )
                    ->first();

                if ($existingOrder instanceof Order) {
                    return $existingOrder;
                }

                [
                    $calculation,
                    $coupon,
                ] = $this->calculateLockedTotals(
                    $sessionCart,
                    $fulfillmentMethod,
                    $validatedAddress,
                );

                $placedAt = now();

                $order = Order::query()->create([
                    'public_id' => (string) Str::ulid(),
                    'user_id' => $user?->id,

                    'customer_name' => $validatedCustomer['name'],

                    'customer_email' => $validatedCustomer['email'],

                    'customer_phone' => $validatedCustomer['phone'],

                    'status' => OrderStatus::PendingConfirmation,

                    'payment_status' => PaymentStatus::Pending,

                    'fulfillment_method' => $fulfillmentMethod,

                    'payment_method' => $paymentMethod,

                    'currency' => 'USD',

                    'subtotal_cents' => (int) $calculation[
                            'subtotal_cents'
                        ],

                    'discount_cents' => (int) $calculation[
                            'discount_cents'
                        ],

                    'tax_cents' => (int) $calculation[
                            'tax_cents'
                        ],

                    'delivery_cents' => (int) $calculation[
                            'delivery_fee_cents'
                        ],

                    'grand_total_cents' => (int) $calculation[
                            'grand_total_cents'
                        ],

                    'tax_rate_basis_points' => (int) $calculation[
                            'tax_rate_basis_points'
                        ],

                    'coupon_code' => $coupon?->code,

                    'coupon_snapshot' => $this->couponSnapshot(
                        $coupon,
                        $calculation,
                    ),

                    'customer_note' => $this->normalizeOptionalText(
                        $customerNote,
                    ),

                    'checkout_idempotency_token' => $normalizedToken,

                    'placed_at' => $placedAt,
                ]);

                $order->forceFill([
                    'order_number' => sprintf(
                        'CC-%s-%06d',
                        $placedAt->format('Ymd'),
                        $order->id,
                    ),
                ])->save();

                /** @var list<array<string, mixed>> $calculatedItems */
                $calculatedItems =
                    $calculation['items'];

                $this->createItemSnapshots(
                    $order,
                    $calculatedItems,
                );

                if ($validatedAddress !== null) {
                    $order->addresses()->create([
                        'type' => 'delivery',
                        ...$validatedAddress,
                    ]);
                }

                $order->statusHistories()->create([
                    'changed_by_user_id' => $user?->id,
                    'previous_status' => null,
                    'new_status' => OrderStatus::PendingConfirmation,
                    'public_note' => 'Your order was received and is awaiting restaurant confirmation.',
                ]);

                $order->payments()->create([
                    'provider' => $paymentMethod->provider(),

                    'payment_method' => $paymentMethod,

                    'status' => PaymentStatus::Pending,

                    'amount_cents' => (int) $calculation[
                            'grand_total_cents'
                        ],

                    'currency' => 'USD',
                ]);

                if ($coupon instanceof Coupon) {
                    $this->recordCouponUsage(
                        $coupon,
                        $order,
                        $user,
                        (int) $calculation[
                            'discount_cents'
                        ],
                    );
                }

                return $order;
            },
            3,
        );

        $sessionCart->clear();

        return $this->loadOrder($order);
    }

    /**
     * Validate and normalize customer contact snapshots.
     *
     * @param  array<string, mixed>  $customer
     * @return array{name: string, email: string, phone: string}
     */
    private function validateCustomer(
        array $customer,
    ): array {
        $validated = Validator::make(
            $customer,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ],
        )->validate();

        return [
            'name' => trim(
                (string) $validated['name'],
            ),

            'email' => Str::lower(
                trim(
                    (string) $validated['email'],
                ),
            ),

            'phone' => trim(
                (string) $validated['phone'],
            ),
        ];
    }

    /**
     * Validate and normalize the delivery-address snapshot.
     *
     * @param  array<string, mixed>|null  $deliveryAddress
     * @return array{
     *     recipient_name: string,
     *     street_address: string,
     *     apartment_or_unit: string|null,
     *     city: string,
     *     state: string,
     *     postal_code: string,
     *     phone: string,
     *     delivery_instructions: string|null
     * }
     */
    private function validateDeliveryAddress(
        ?array $deliveryAddress,
    ): array {
        $validated = Validator::make(
            $deliveryAddress ?? [],
            [
                'recipient_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'street_address' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'apartment_or_unit' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'city' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'state' => [
                    'required',
                    'string',
                    'size:2',
                ],

                'postal_code' => [
                    'required',
                    'regex:/^\d{5}$/',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'delivery_instructions' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
        )->validate();

        return [
            'recipient_name' => trim(
                (string) $validated[
                    'recipient_name'
                ],
            ),

            'street_address' => trim(
                (string) $validated[
                    'street_address'
                ],
            ),

            'apartment_or_unit' => $this->normalizeOptionalText(
                $validated[
                    'apartment_or_unit'
                ] ?? null,
            ),

            'city' => trim(
                (string) $validated['city'],
            ),

            'state' => Str::upper(
                trim(
                    (string) $validated['state'],
                ),
            ),

            'postal_code' => trim(
                (string) $validated[
                    'postal_code'
                ],
            ),

            'phone' => trim(
                (string) $validated['phone'],
            ),

            'delivery_instructions' => $this->normalizeOptionalText(
                $validated[
                    'delivery_instructions'
                ] ?? null,
            ),
        ];
    }

    /**
     * Validate the checkout idempotency token.
     */
    private function validateIdempotencyToken(
        string $idempotencyToken,
    ): string {
        $normalized = trim($idempotencyToken);

        if (! Str::isUuid($normalized)) {
            throw ValidationException::withMessages([
                'checkout' => 'The checkout session has expired. Refresh the page and try again.',
            ]);
        }

        return $normalized;
    }

    /**
     * Enforce configured payment methods for the selected fulfillment method.
     */
    private function assertPaymentMethodAvailable(
        FulfillmentMethod $fulfillmentMethod,
        PaymentMethod $paymentMethod,
    ): void {
        if ($paymentMethod === PaymentMethod::Stripe) {
            if (
                ! StripeCheckoutService::isConfigured()
            ) {
                throw ValidationException::withMessages([
                    'paymentMethod' => 'Online card payment is not currently available.',
                ]);
            }

            return;
        }

        if (
            $fulfillmentMethod
                === FulfillmentMethod::Pickup
            && (
                $paymentMethod
                    !== PaymentMethod::CashAtPickup
                || ! SiteSetting::cashAtPickupEnabled()
            )
        ) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Cash at pickup is not currently available.',
            ]);
        }

        if (
            $fulfillmentMethod
                === FulfillmentMethod::Delivery
            && (
                $paymentMethod
                    !== PaymentMethod::CashOnDelivery
                || ! SiteSetting::cashOnDeliveryEnabled()
            )
        ) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Cash on delivery is not currently available.',
            ]);
        }
    }

    /**
     * Lock and revalidate coupon usage before final price calculation.
     *
     * @param  array<string, mixed>|null  $deliveryAddress
     * @return array{
     *     0: array<string, mixed>,
     *     1: Coupon|null
     * }
     */
    private function calculateLockedTotals(
        SessionCart $sessionCart,
        FulfillmentMethod $fulfillmentMethod,
        ?array $deliveryAddress,
    ): array {
        $deliveryZip =
            $deliveryAddress['postal_code'] ?? null;

        $baseCalculation =
            $this->totalsCalculator->calculate(
                $sessionCart->items(),
                null,
                $fulfillmentMethod,
                is_string($deliveryZip)
                    ? $deliveryZip
                    : null,
            );

        $this->assertCalculationReady(
            $baseCalculation,
        );

        $couponCode =
            $sessionCart->couponCode();

        if ($couponCode === null) {
            return [
                $baseCalculation,
                null,
            ];
        }

        $coupon = Coupon::query()
            ->where(
                'code',
                Coupon::normalizeCode(
                    $couponCode,
                ),
            )
            ->lockForUpdate()
            ->first();

        if (! $coupon instanceof Coupon) {
            throw ValidationException::withMessages([
                'couponCode' => 'The coupon code is invalid.',
            ]);
        }

        $couponError = $coupon->validationMessage(
            (int) $baseCalculation[
                'subtotal_cents'
            ],
        );

        if ($couponError !== null) {
            throw ValidationException::withMessages([
                'couponCode' => $couponError,
            ]);
        }

        $calculation =
            $this->totalsCalculator->calculate(
                $sessionCart->items(),
                $coupon->code,
                $fulfillmentMethod,
                is_string($deliveryZip)
                    ? $deliveryZip
                    : null,
            );

        $this->assertCalculationReady(
            $calculation,
        );

        return [
            $calculation,
            $coupon,
        ];
    }

    /**
     * Convert calculator failures into checkout validation failures.
     *
     * @param  array<string, mixed>  $calculation
     */
    private function assertCalculationReady(
        array $calculation,
    ): void {
        if (
            ($calculation[
                'invalid_line_keys'
            ] ?? []) !== []
        ) {
            throw ValidationException::withMessages([
                'cart' => 'One or more items are no longer available. Review your cart before checking out.',
            ]);
        }

        if (
            ($calculation['items'] ?? [])
            === []
        ) {
            throw ValidationException::withMessages([
                'cart' => 'Your shopping cart is empty.',
            ]);
        }

        $couponError =
            $calculation['coupon_error']
                ?? null;

        if (is_string($couponError)) {
            throw ValidationException::withMessages([
                'couponCode' => $couponError,
            ]);
        }

        $fulfillmentError =
            $calculation[
                'fulfillment_error'
            ] ?? null;

        if (is_string($fulfillmentError)) {
            throw ValidationException::withMessages([
                'deliveryPostalCode' => $fulfillmentError,
            ]);
        }

        if (
            ! (
                $calculation[
                    'accepting_online_orders'
                ] ?? false
            )
        ) {
            throw ValidationException::withMessages([
                'cart' => (string) (
                    $calculation[
                        'online_orders_closed_message'
                    ]
                    ?? 'Online ordering is currently unavailable.'
                ),
            ]);
        }

        if (
            ! (
                $calculation[
                    'is_checkout_ready'
                ] ?? false
            )
        ) {
            throw ValidationException::withMessages([
                'checkout' => 'The order cannot currently be submitted.',
            ]);
        }
    }

    /**
     * Create immutable item and option snapshots.
     *
     * @param  list<array<string, mixed>>  $calculatedItems
     */
    private function createItemSnapshots(
        Order $order,
        array $calculatedItems,
    ): void {
        $menuItemIds = array_map(
            static fn (array $item): int => (int) $item['menu_item_id'],
            $calculatedItems,
        );

        $menuItems = MenuItem::query()
            ->whereIn('id', $menuItemIds)
            ->get()
            ->keyBy('id');

        foreach ($calculatedItems as $item) {
            /** @var list<array<string, mixed>> $options */
            $options = $item['options'];

            $optionSnapshots = array_map(
                static fn (array $option): array => [
                    'id' => (int) $option['id'],
                    'group_name' => (string) $option[
                            'group_name'
                        ],
                    'name' => (string) $option['name'],
                    'additional_price_cents' => (int) $option[
                            'additional_price_cents'
                        ],
                ],
                $options,
            );

            $optionTotalCents = array_sum(
                array_map(
                    static fn (
                        array $option,
                    ): int => (int) $option[
                        'additional_price_cents'
                    ],
                    $optionSnapshots,
                ),
            );

            $unitPriceCents =
                (int) $item[
                    'unit_price_cents'
                ];

            $menuItem = $menuItems->get(
                (int) $item['menu_item_id'],
            );

            $order->items()->create([
                'menu_item_id' => (int) $item[
                        'menu_item_id'
                    ],

                'name' => (string) $item['name'],

                'description' => $menuItem instanceof MenuItem
                    ? $menuItem->description
                    : null,

                'base_unit_price_cents' => max(
                    0,
                    $unitPriceCents
                        - $optionTotalCents,
                ),

                'quantity' => (int) $item['quantity'],

                'selected_options' => $optionSnapshots,

                'option_total_cents' => $optionTotalCents,

                'unit_price_cents' => $unitPriceCents,

                'line_total_cents' => (int) $item[
                        'line_total_cents'
                    ],
            ]);
        }
    }

    /**
     * Snapshot the coupon configuration and actual applied discount.
     *
     * @param  array<string, mixed>  $calculation
     * @return array<string, int|string|null>|null
     */
    private function couponSnapshot(
        ?Coupon $coupon,
        array $calculation,
    ): ?array {
        if (! $coupon instanceof Coupon) {
            return null;
        }

        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type->value,
            'fixed_discount_cents' => $coupon->fixed_discount_cents,
            'percentage_basis_points' => $coupon->percentage_basis_points,
            'discount_cents' => (int) $calculation[
                    'discount_cents'
                ],
        ];
    }

    /**
     * Increment the locked coupon and create its immutable usage record.
     */
    private function recordCouponUsage(
        Coupon $coupon,
        Order $order,
        ?User $user,
        int $discountCents,
    ): void {
        $coupon->forceFill([
            'times_used' => $coupon->times_used + 1,
        ])->save();

        CouponUsage::query()->create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'code' => $coupon->code,
            'discount_cents' => $discountCents,
            'used_at' => now(),
        ]);
    }

    /**
     * Trim optional text and normalize blank values to null.
     */
    private function normalizeOptionalText(
        mixed $value,
    ): ?string {
        $normalized = trim(
            (string) $value,
        );

        return $normalized === ''
            ? null
            : $normalized;
    }

    /**
     * Load the complete order aggregate required after checkout.
     */
    private function loadOrder(
        Order $order,
    ): Order {
        return $order->load([
            'items',
            'addresses',
            'payments',
            'statusHistories',
            'couponUsage',
        ]);
    }
}
