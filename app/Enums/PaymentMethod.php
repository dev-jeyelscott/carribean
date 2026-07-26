<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Stripe = 'stripe';
    case CashAtPickup = 'cash_at_pickup';
    case CashOnDelivery = 'cash_on_delivery';

    /**
     * Return the customer-facing payment-method label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Pay online',
            self::CashAtPickup => 'Cash at pickup',
            self::CashOnDelivery => 'Cash on delivery',
        };
    }

    /**
     * Return the provider stored on the payment record.
     */
    public function provider(): string
    {
        return $this === self::Stripe
            ? 'stripe'
            : 'cash';
    }

    /**
     * Determine whether the payment is collected in person.
     */
    public function isCash(): bool
    {
        return match ($this) {
            self::CashAtPickup,
            self::CashOnDelivery => true,
            self::Stripe => false,
        };
    }
}
