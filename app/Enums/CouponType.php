<?php

namespace App\Enums;

enum CouponType: string
{
    case FixedAmount = 'fixed';
    case Percentage = 'percentage';

    /**
     * Return the administrator-facing label for this coupon type.
     */
    public function label(): string
    {
        return match ($this) {
            self::FixedAmount => 'Fixed amount',
            self::Percentage => 'Percentage',
        };
    }

    /**
     * Return options suitable for Filament select fields.
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
