<?php

namespace App\Support;

use InvalidArgumentException;

final class Rate
{
    public const MAX_BASIS_POINTS = 10_000;

    /**
     * Convert a percentage such as 8.25 into 825 basis points.
     */
    public static function percentToBasisPoints(
        string|int|float|null $percentage,
    ): ?int {
        if ($percentage === null || $percentage === '') {
            return null;
        }

        $normalized = trim((string) $percentage);

        if (! preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException(
                'Percentages must use no more than two decimal places.',
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $normalized, 2),
            2,
            '',
        );

        $basisPoints = ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');

        if ($basisPoints > self::MAX_BASIS_POINTS) {
            throw new InvalidArgumentException(
                'Percentages cannot exceed 100 percent.',
            );
        }

        return $basisPoints;
    }

    /**
     * Convert basis points into a decimal percentage string.
     */
    public static function basisPointsToPercent(
        ?int $basisPoints,
    ): ?string {
        if ($basisPoints === null) {
            return null;
        }

        self::assertValidBasisPoints($basisPoints);

        return sprintf(
            '%d.%02d',
            intdiv($basisPoints, 100),
            $basisPoints % 100,
        );
    }

    /**
     * Format basis points as a customer-facing percentage.
     */
    public static function formatBasisPoints(int $basisPoints): string
    {
        $percentage = self::basisPointsToPercent($basisPoints)
            ?? '0.00';

        return rtrim(rtrim($percentage, '0'), '.').'%';
    }

    /**
     * Apply basis points to a non-negative integer amount.
     *
     * Rounds to the nearest cent without using floating-point arithmetic.
     */
    public static function applyBasisPoints(
        int $amountCents,
        int $basisPoints,
    ): int {
        if ($amountCents < 0) {
            throw new InvalidArgumentException(
                'The amount cannot be negative.',
            );
        }

        self::assertValidBasisPoints($basisPoints);

        return intdiv(
            ($amountCents * $basisPoints) + 5_000,
            self::MAX_BASIS_POINTS,
        );
    }

    /**
     * Ensure a basis-point value represents zero through 100 percent.
     */
    private static function assertValidBasisPoints(
        int $basisPoints,
    ): void {
        if (
            $basisPoints < 0
            || $basisPoints > self::MAX_BASIS_POINTS
        ) {
            throw new InvalidArgumentException(
                'Basis points must be between 0 and 10000.',
            );
        }
    }
}
