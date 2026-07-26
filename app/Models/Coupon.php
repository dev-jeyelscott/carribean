<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Support\Money;
use App\Support\Rate;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property string $code
 * @property CouponType $type
 * @property int|null $fixed_discount_cents
 * @property int|null $percentage_basis_points
 * @property int $minimum_subtotal_cents
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property int|null $usage_limit
 * @property int $times_used
 * @property bool $is_active
 */
#[Fillable([
    'code',
    'type',
    'fixed_discount_cents',
    'percentage_basis_points',
    'minimum_subtotal_cents',
    'starts_at',
    'expires_at',
    'usage_limit',
    'times_used',
    'is_active',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    /**
     * Configure coupon attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'fixed_discount_cents' => 'integer',
            'percentage_basis_points' => 'integer',
            'minimum_subtotal_cents' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Normalize coupon codes and clear values belonging to another type.
     */
    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon): void {
            $coupon->code = self::normalizeCode($coupon->code);

            if ($coupon->type === CouponType::FixedAmount) {
                $coupon->percentage_basis_points = null;

                return;
            }

            $coupon->fixed_discount_cents = null;
        });
    }

    /**
     * Normalize customer and administrator coupon input.
     */
    public static function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    /**
     * Return the reason this coupon cannot be applied.
     */
    public function validationMessage(
        int $subtotalCents,
        ?Carbon $at = null,
    ): ?string {
        $at ??= now();

        if (! $this->is_active) {
            return 'This coupon is not currently active.';
        }

        if ($this->starts_at?->isAfter($at)) {
            return 'This coupon is not active yet.';
        }

        if ($this->expires_at?->isBefore($at)) {
            return 'This coupon has expired.';
        }

        if (
            $this->usage_limit !== null
            && $this->times_used >= $this->usage_limit
        ) {
            return 'This coupon has reached its usage limit.';
        }

        if ($subtotalCents < $this->minimum_subtotal_cents) {
            return sprintf(
                'This coupon requires a minimum subtotal of %s.',
                Money::formatUsd(
                    $this->minimum_subtotal_cents,
                ) ?? '$0.00',
            );
        }

        if (
            $this->type === CouponType::FixedAmount
            && $this->fixed_discount_cents === null
        ) {
            return 'This coupon does not have a valid discount amount.';
        }

        if (
            $this->type === CouponType::Percentage
            && $this->percentage_basis_points === null
        ) {
            return 'This coupon does not have a valid percentage.';
        }

        return null;
    }

    /**
     * Calculate this coupon's discount against a merchandise subtotal.
     */
    public function discountCentsFor(int $subtotalCents): int
    {
        $message = $this->validationMessage($subtotalCents);

        if ($message !== null) {
            throw ValidationException::withMessages([
                'couponCode' => $message,
            ]);
        }

        $discountCents = match ($this->type) {
            CouponType::FixedAmount => min(
                $subtotalCents,
                $this->fixed_discount_cents ?? 0,
            ),
            CouponType::Percentage => Rate::applyBasisPoints(
                $subtotalCents,
                $this->percentage_basis_points ?? 0,
            ),
        };

        return min($subtotalCents, $discountCents);
    }

    /**
     * Format this coupon's configured discount for administration.
     */
    public function formattedDiscount(): string
    {
        return match ($this->type) {
            CouponType::FixedAmount => Money::formatUsd(
                $this->fixed_discount_cents,
            ) ?? '$0.00',
            CouponType::Percentage => Rate::formatBasisPoints(
                $this->percentage_basis_points ?? 0,
            ),
        };
    }
}
