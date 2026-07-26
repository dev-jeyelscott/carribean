<?php

namespace App\Enums;

enum FulfillmentMethod: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    /**
     * Return the customer-facing label for this fulfillment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Pickup',
            self::Delivery => 'Local delivery',
        };
    }

    /**
     * Return options suitable for form controls.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
