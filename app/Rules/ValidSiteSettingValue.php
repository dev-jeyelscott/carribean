<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ValidSiteSettingValue implements ValidationRule
{
    /**
     * @var list<string>
     */
    private const HTTP_URL_KEYS = [
        'map_link',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
    ];

    /**
     * @var list<string>
     */
    private const EMAIL_KEYS = [
        'email',
        'notification_recipient_email',
    ];

    /**
     * @var list<string>
     */
    private const BOOLEAN_KEYS = [
        'accepting_online_orders',
        'cash_at_pickup_enabled',
        'cash_on_delivery_enabled',
    ];

    /**
     * @var list<string>
     */
    private const NON_NEGATIVE_INTEGER_KEYS = [
        'delivery_fee_cents',
        'delivery_minimum_cents',
        'tax_rate_basis_points',
    ];

    public function __construct(private ?string $key) {}

    /**
     * Validate one setting according to its key.
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $message = $this->validationMessage($value);

        if ($message !== null) {
            $fail($message);
        }
    }

    /**
     * Return settings that are exposed as public links.
     *
     * @return list<string>
     */
    public static function publicLinkKeys(): array
    {
        return [
            'phone',
            'email',
            ...self::HTTP_URL_KEYS,
        ];
    }

    /**
     * Determine whether a key and value combination is valid.
     */
    public static function accepts(
        ?string $key,
        mixed $value,
    ): bool {
        return (new self($key))
            ->validationMessage($value) === null;
    }

    /**
     * Return a validation message or null when the value is accepted.
     */
    private function validationMessage(
        mixed $value,
    ): ?string {
        if (! is_string($value)) {
            return 'The :attribute must be a string.';
        }

        if (
            in_array($this->key, self::EMAIL_KEYS, true)
            && ! $this->isValidEmail($value)
        ) {
            return 'The :attribute must be a valid email address.';
        }

        if (
            $this->key === 'phone'
            && ! $this->isValidPhone($value)
        ) {
            return 'The :attribute may only contain digits, spaces, plus signs, hyphens, periods, and parentheses.';
        }

        if (
            in_array($this->key, self::HTTP_URL_KEYS, true)
            && ! $this->isValidHttpUrl($value)
        ) {
            return 'The :attribute must be a valid HTTP or HTTPS URL.';
        }

        if (
            in_array($this->key, self::BOOLEAN_KEYS, true)
            && ! in_array(
                strtolower(trim($value)),
                ['0', '1', 'true', 'false'],
                true,
            )
        ) {
            return 'The :attribute must be 1, 0, true, or false.';
        }

        if (
            in_array(
                $this->key,
                self::NON_NEGATIVE_INTEGER_KEYS,
                true,
            )
            && preg_match('/^\d+$/', trim($value)) !== 1
        ) {
            return 'The :attribute must be a non-negative whole number.';
        }

        if (
            $this->key === 'tax_rate_basis_points'
            && preg_match('/^\d+$/', trim($value)) === 1
            && (int) $value > 10_000
        ) {
            return 'The tax rate cannot exceed 10000 basis points.';
        }

        if (
            $this->key === 'accepted_delivery_zip_codes'
            && ! $this->isValidZipCodeList($value)
        ) {
            return 'Enter five-digit ZIP codes separated by commas, spaces, or new lines.';
        }

        if (
            $this->key === 'online_orders_closed_message'
            && mb_strlen($value) > 500
        ) {
            return 'The closed message may not exceed 500 characters.';
        }

        return null;
    }

    /**
     * Validate an email setting.
     */
    private function isValidEmail(string $value): bool
    {
        return strlen($value) <= 254
            && filter_var(
                $value,
                FILTER_VALIDATE_EMAIL,
            ) !== false;
    }

    /**
     * Validate a public telephone number.
     */
    private function isValidPhone(string $value): bool
    {
        return strlen($value) <= 40
            && preg_match(
                '/\A(?=.*[0-9])[0-9+().\- ]+\z/',
                $value,
            ) === 1;
    }

    /**
     * Validate a public HTTP or HTTPS URL.
     */
    private function isValidHttpUrl(string $value): bool
    {
        if (
            strlen($value) > 2048
            || filter_var(
                $value,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        return is_string($scheme)
            && in_array(
                strtolower($scheme),
                ['http', 'https'],
                true,
            );
    }

    /**
     * Validate a comma-, whitespace-, or line-separated ZIP-code list.
     */
    private function isValidZipCodeList(
        string $value,
    ): bool {
        if (trim($value) === '') {
            return true;
        }

        $zipCodes = preg_split(
            '/[\s,]+/',
            trim($value),
            -1,
            PREG_SPLIT_NO_EMPTY,
        );

        if (! is_array($zipCodes)) {
            return false;
        }

        foreach ($zipCodes as $zipCode) {
            if (preg_match('/^\d{5}$/', $zipCode) !== 1) {
                return false;
            }
        }

        return true;
    }
}
