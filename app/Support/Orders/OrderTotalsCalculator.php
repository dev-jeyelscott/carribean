<?php

namespace App\Support\Orders;

use App\Enums\FulfillmentMethod;
use App\Models\Coupon;
use App\Models\SiteSetting;
use App\Support\Money;
use App\Support\Rate;
use Illuminate\Validation\ValidationException;

final readonly class OrderTotalsCalculator
{
    /**
     * Receive the existing authoritative item calculator.
     */
    public function __construct(
        private OrderPriceCalculator $itemPriceCalculator,
    ) {}

    /**
     * Validate one submitted coupon code against a subtotal.
     */
    public function validateCouponCode(
        string $couponCode,
        int $subtotalCents,
    ): Coupon {
        $normalizedCode = Coupon::normalizeCode(
            $couponCode,
        );

        $coupon = Coupon::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon instanceof Coupon) {
            throw ValidationException::withMessages([
                'couponCode' => 'The coupon code is invalid.',
            ]);
        }

        $message = $coupon->validationMessage(
            $subtotalCents,
        );

        if ($message !== null) {
            throw ValidationException::withMessages([
                'couponCode' => $message,
            ]);
        }

        return $coupon;
    }

    /**
     * Calculate merchandise, coupon, tax, delivery, and grand total.
     *
     * @param  array<string, array{
     *     menu_item_id: int,
     *     option_ids: list<int>,
     *     quantity: int
     * }>  $cartItems
     * @return array<string, mixed>
     */
    public function calculate(
        array $cartItems,
        ?string $couponCode = null,
        FulfillmentMethod $fulfillmentMethod =
        FulfillmentMethod::Pickup,
        ?string $deliveryZip = null,
    ): array {
        $itemCalculation =
            $this->itemPriceCalculator->calculate(
                $cartItems,
            );

        $subtotalCents =
            $itemCalculation['subtotal_cents'];

        $couponCalculation = $this->resolveCoupon(
            $couponCode,
            $subtotalCents,
        );

        $discountCents =
            $couponCalculation['discount_cents'];

        $discountedSubtotalCents = max(
            0,
            $subtotalCents - $discountCents,
        );

        $taxRateBasisPoints =
            SiteSetting::taxRateBasisPoints();

        $taxCents = Rate::applyBasisPoints(
            $discountedSubtotalCents,
            $taxRateBasisPoints,
        );

        $fulfillmentCalculation =
            $this->resolveFulfillment(
                $fulfillmentMethod,
                $deliveryZip,
                $discountedSubtotalCents,
            );

        $deliveryFeeCents =
            $fulfillmentCalculation['delivery_fee_cents'];

        $grandTotalCents =
            $discountedSubtotalCents
            + $taxCents
            + $deliveryFeeCents;

        $acceptingOnlineOrders =
            SiteSetting::acceptingOnlineOrders();

        $isCheckoutReady =
            $itemCalculation['items'] !== []
            && $itemCalculation['invalid_line_keys'] === []
            && $couponCalculation['error'] === null
            && $fulfillmentCalculation['error'] === null
            && $acceptingOnlineOrders;

        return [
            ...$itemCalculation,

            'coupon' => $couponCalculation['coupon'],
            'coupon_code' => $couponCalculation['code'],
            'coupon_error' => $couponCalculation['error'],

            'discount_cents' => $discountCents,
            'formatted_discount' => Money::formatUsd(
                $discountCents,
            ) ?? '$0.00',

            'discounted_subtotal_cents' => $discountedSubtotalCents,

            'formatted_discounted_subtotal' => Money::formatUsd(
                $discountedSubtotalCents,
            ) ?? '$0.00',

            'tax_rate_basis_points' => $taxRateBasisPoints,

            'formatted_tax_rate' => Rate::formatBasisPoints(
                $taxRateBasisPoints,
            ),

            'tax_cents' => $taxCents,

            'formatted_tax' => Money::formatUsd(
                $taxCents,
            ) ?? '$0.00',

            'fulfillment_method' => $fulfillmentMethod->value,

            'fulfillment_label' => $fulfillmentMethod->label(),

            'delivery_zip' => $fulfillmentCalculation['delivery_zip'],

            'delivery_fee_cents' => $deliveryFeeCents,

            'formatted_delivery_fee' => Money::formatUsd(
                $deliveryFeeCents,
            ) ?? '$0.00',

            'delivery_minimum_cents' => SiteSetting::deliveryMinimumCents(),

            'formatted_delivery_minimum' => Money::formatUsd(
                SiteSetting::deliveryMinimumCents(),
            ) ?? '$0.00',

            'fulfillment_error' => $fulfillmentCalculation['error'],

            'grand_total_cents' => $grandTotalCents,

            'formatted_grand_total' => Money::formatUsd(
                $grandTotalCents,
            ) ?? '$0.00',

            'accepting_online_orders' => $acceptingOnlineOrders,

            'online_orders_closed_message' => SiteSetting::onlineOrdersClosedMessage(),

            'is_checkout_ready' => $isCheckoutReady,
        ];
    }

    /**
     * Resolve a coupon without allowing an invalid coupon to alter totals.
     *
     * @return array{
     *     coupon: array<string, mixed>|null,
     *     code: string|null,
     *     error: string|null,
     *     discount_cents: int
     * }
     */
    private function resolveCoupon(
        ?string $couponCode,
        int $subtotalCents,
    ): array {
        $normalizedCode = Coupon::normalizeCode(
            $couponCode,
        );

        if ($normalizedCode === '') {
            return [
                'coupon' => null,
                'code' => null,
                'error' => null,
                'discount_cents' => 0,
            ];
        }

        $coupon = Coupon::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon instanceof Coupon) {
            return [
                'coupon' => null,
                'code' => $normalizedCode,
                'error' => 'The coupon code is invalid.',
                'discount_cents' => 0,
            ];
        }

        $message = $coupon->validationMessage(
            $subtotalCents,
        );

        if ($message !== null) {
            return [
                'coupon' => null,
                'code' => $normalizedCode,
                'error' => $message,
                'discount_cents' => 0,
            ];
        }

        $discountCents = $coupon->discountCentsFor(
            $subtotalCents,
        );

        return [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type->value,
                'formatted_value' => $coupon->formattedDiscount(),
            ],
            'code' => $coupon->code,
            'error' => null,
            'discount_cents' => $discountCents,
        ];
    }

    /**
     * Resolve pickup or ZIP-based local-delivery pricing.
     *
     * @return array{
     *     delivery_zip: string|null,
     *     delivery_fee_cents: int,
     *     error: string|null
     * }
     */
    private function resolveFulfillment(
        FulfillmentMethod $fulfillmentMethod,
        ?string $deliveryZip,
        int $discountedSubtotalCents,
    ): array {
        if (
            $fulfillmentMethod
            === FulfillmentMethod::Pickup
        ) {
            return [
                'delivery_zip' => null,
                'delivery_fee_cents' => 0,
                'error' => null,
            ];
        }

        $normalizedZip =
            SiteSetting::normalizeDeliveryZip(
                $deliveryZip,
            );

        if ($normalizedZip === null) {
            return [
                'delivery_zip' => null,
                'delivery_fee_cents' => 0,
                'error' => 'Enter a valid five-digit delivery ZIP code.',
            ];
        }

        if (
            SiteSetting::acceptedDeliveryZipCodes()
            === []
        ) {
            return [
                'delivery_zip' => $normalizedZip,
                'delivery_fee_cents' => 0,
                'error' => 'Local delivery is not currently configured.',
            ];
        }

        if (
            ! SiteSetting::acceptsDeliveryZip(
                $normalizedZip,
            )
        ) {
            return [
                'delivery_zip' => $normalizedZip,
                'delivery_fee_cents' => 0,
                'error' => 'Local delivery is not available for this ZIP code.',
            ];
        }

        $deliveryMinimumCents =
            SiteSetting::deliveryMinimumCents();

        if (
            $discountedSubtotalCents
            < $deliveryMinimumCents
        ) {
            return [
                'delivery_zip' => $normalizedZip,
                'delivery_fee_cents' => 0,
                'error' => sprintf(
                    'Local delivery requires a minimum merchandise total of %s after discounts.',
                    Money::formatUsd(
                        $deliveryMinimumCents,
                    ) ?? '$0.00',
                ),
            ];
        }

        return [
            'delivery_zip' => $normalizedZip,
            'delivery_fee_cents' => SiteSetting::deliveryFeeCents(),
            'error' => null,
        ];
    }
}
