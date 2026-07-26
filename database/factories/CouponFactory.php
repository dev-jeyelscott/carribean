<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define a valid fixed-amount coupon.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(
                fake()->unique()->bothify('SAVE-##-??'),
            ),
            'type' => CouponType::FixedAmount,
            'fixed_discount_cents' => 500,
            'percentage_basis_points' => null,
            'minimum_subtotal_cents' => 2_000,
            'starts_at' => null,
            'expires_at' => null,
            'usage_limit' => null,
            'times_used' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Configure the factory to create a percentage coupon.
     */
    public function percentage(
        int $basisPoints = 1_000,
    ): static {
        return $this->state(fn (): array => [
            'type' => CouponType::Percentage,
            'fixed_discount_cents' => null,
            'percentage_basis_points' => $basisPoints,
        ]);
    }

    /**
     * Configure the factory to create an expired coupon.
     */
    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Configure the factory to create an inactive coupon.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
