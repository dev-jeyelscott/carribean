<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    /**
     * Convert a non-negative decimal money value into integer cents.
     */
    public static function decimalToCents(
        string|int|float|null $amount,
    ): ?int {
        if ($amount === null || $amount === '') {
            return null;
        }

        $normalized = trim((string) $amount);

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException(
                'Money values must be non-negative and use at most two decimal places.',
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $normalized, 2),
            2,
            '',
        );

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

    /**
     * Convert integer cents into a database-compatible decimal string.
     */
    public static function centsToDecimal(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        if ($cents < 0) {
            throw new InvalidArgumentException(
                'Money values cannot be negative.',
            );
        }

        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100,
        );
    }

    /**
     * Format integer cents as United States dollars.
     */
    public static function formatUsd(?int $cents): ?string
    {
        $decimal = self::centsToDecimal($cents);

        return $decimal === null
            ? null
            : '$'.$decimal;
    }
}
