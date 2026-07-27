<?php

use App\Support\Money;

test('it converts decimal values into integer cents', function (): void {
    expect(Money::decimalToCents('18.50'))->toBe(1850)
        ->and(Money::decimalToCents('18'))->toBe(1800)
        ->and(Money::decimalToCents('0.05'))->toBe(5)
        ->and(Money::decimalToCents(null))->toBeNull();
});

test('it converts cents into decimal strings', function (): void {
    expect(Money::centsToDecimal(1850))->toBe('18.50')
        ->and(Money::centsToDecimal(5))->toBe('0.05')
        ->and(Money::centsToDecimal(null))->toBeNull();
});

test('it formats cents as united states dollars', function (): void {
    expect(Money::formatUsd(1850))->toBe('$18.50')
        ->and(Money::formatUsd(5))->toBe('$0.05')
        ->and(Money::formatUsd(null))->toBeNull();
});

test('it rejects invalid money values', function (): void {
    Money::decimalToCents('12.999');
})->throws(InvalidArgumentException::class);
