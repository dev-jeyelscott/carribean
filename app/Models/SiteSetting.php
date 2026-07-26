<?php

namespace App\Models;

use App\Rules\ValidSiteSettingValue;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 * @property string $group
 */
#[Fillable(['key', 'value', 'group'])]
class SiteSetting extends Model
{
    private const CACHE_KEY = 'site-settings.key-value-map';

    /**
     * Clear cached settings whenever an administrator changes them.
     */
    protected static function booted(): void
    {
        static::saved(static function (): void {
            static::forgetCachedValues();
        });

        static::deleted(static function (): void {
            static::forgetCachedValues();
        });
    }

    /**
     * Restrict settings to one administration group.
     *
     * @param  Builder<SiteSetting>  $query
     * @return Builder<SiteSetting>
     */
    public function scopeGroup(
        Builder $query,
        string $group,
    ): Builder {
        return $query->where('group', $group);
    }

    /**
     * Return one cached raw setting value.
     */
    public static function value(
        string $key,
        ?string $default = null,
    ): ?string {
        return static::keyValueMap()[$key] ?? $default;
    }

    /**
     * Return all cached settings indexed by key.
     *
     * @return array<string, string|null>
     */
    public static function keyValueMap(): array
    {
        /** @var array<string, string|null> $settings */
        $settings = Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => static::query()
                ->pluck('value', 'key')
                ->all(),
        );

        return $settings;
    }

    /**
     * Remove the shared site-setting cache.
     */
    public static function forgetCachedValues(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Return whether checkout is currently allowed.
     */
    public static function acceptingOnlineOrders(): bool
    {
        return static::booleanValue(
            'accepting_online_orders',
            false,
        );
    }

    /**
     * Return the message displayed when checkout is paused.
     */
    public static function onlineOrdersClosedMessage(): string
    {
        return static::value(
            'online_orders_closed_message',
            'Online ordering is temporarily unavailable.',
        ) ?? 'Online ordering is temporarily unavailable.';
    }

    /**
     * Return normalized ZIP codes accepted for local delivery.
     *
     * @return list<string>
     */
    public static function acceptedDeliveryZipCodes(): array
    {
        $value = static::value(
            'accepted_delivery_zip_codes',
            '',
        );

        if ($value === null || trim($value) === '') {
            return [];
        }

        $zipCodes = preg_split(
            '/[\s,]+/',
            trim($value),
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        if (! is_array($zipCodes)) {
            return [];
        }

        $normalized = [];

        foreach ($zipCodes as $zipCode) {
            $validZipCode = static::normalizeDeliveryZip(
                $zipCode,
            );

            if ($validZipCode !== null) {
                $normalized[] = $validZipCode;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Determine whether local delivery accepts the supplied ZIP code.
     */
    public static function acceptsDeliveryZip(
        ?string $zipCode,
    ): bool {
        $normalized = static::normalizeDeliveryZip($zipCode);

        return $normalized !== null
            && in_array(
                $normalized,
                static::acceptedDeliveryZipCodes(),
                true,
            );
    }

    /**
     * Normalize a United States five-digit delivery ZIP code.
     */
    public static function normalizeDeliveryZip(
        ?string $zipCode,
    ): ?string {
        $normalized = trim((string) $zipCode);

        return preg_match('/^\d{5}$/', $normalized) === 1
            ? $normalized
            : null;
    }

    /**
     * Return the configured flat local-delivery fee.
     */
    public static function deliveryFeeCents(): int
    {
        return static::nonNegativeIntegerValue(
            'delivery_fee_cents',
        );
    }

    /**
     * Return the discounted merchandise minimum for delivery.
     */
    public static function deliveryMinimumCents(): int
    {
        return static::nonNegativeIntegerValue(
            'delivery_minimum_cents',
        );
    }

    /**
     * Return the configured tax rate using integer basis points.
     */
    public static function taxRateBasisPoints(): int
    {
        return min(
            10_000,
            static::nonNegativeIntegerValue(
                'tax_rate_basis_points',
            ),
        );
    }

    /**
     * Return whether customers may pay cash when collecting pickup.
     */
    public static function cashAtPickupEnabled(): bool
    {
        return static::booleanValue(
            'cash_at_pickup_enabled',
            true,
        );
    }

    /**
     * Return whether customers may pay cash upon delivery.
     */
    public static function cashOnDeliveryEnabled(): bool
    {
        return static::booleanValue(
            'cash_on_delivery_enabled',
            false,
        );
    }

    /**
     * Return customer-facing pickup instructions.
     */
    public static function pickupInstructions(): ?string
    {
        return static::value('pickup_instructions');
    }

    /**
     * Return customer-facing delivery instructions.
     */
    public static function deliveryInstructions(): ?string
    {
        return static::value('delivery_instructions');
    }

    /**
     * Return public contact settings while excluding malformed links.
     *
     * @return array<string, string|null>
     */
    public static function publicContactMap(): array
    {
        $settings = static::keyValueMap();

        foreach (
            ValidSiteSettingValue::publicLinkKeys() as $key
        ) {
            if (
                array_key_exists($key, $settings)
                && ! ValidSiteSettingValue::accepts(
                    $key,
                    $settings[$key],
                )
            ) {
                unset($settings[$key]);
            }
        }

        return $settings;
    }

    /**
     * Parse a boolean-compatible site-setting value.
     */
    private static function booleanValue(
        string $key,
        bool $default,
    ): bool {
        $value = static::value(
            $key,
            $default ? '1' : '0',
        );

        if ($value === null) {
            return $default;
        }

        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );

        return $parsed ?? $default;
    }

    /**
     * Parse a non-negative integer site-setting value.
     */
    private static function nonNegativeIntegerValue(
        string $key,
        int $default = 0,
    ): int {
        $value = static::value($key, (string) $default);

        if (
            $value === null
            || preg_match('/^\d+$/', $value) !== 1
        ) {
            return $default;
        }

        return max(0, (int) $value);
    }
}
