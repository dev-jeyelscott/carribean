@props([
    'status',
])

@php
    use App\Enums\OrderStatus;

    /*
     * Normalize enum and string status values into one reusable public badge.
     */
    $statusValue = $status instanceof OrderStatus
        ? $status->value
        : (string) $status;

    $statusLabel = $status instanceof OrderStatus
        ? $status->label()
        : str($statusValue)
            ->replace('_', ' ')
            ->title()
            ->toString();
@endphp

<span
    {{ $attributes->class(['order-status-pill']) }}
    data-order-status="{{ $statusValue }}">
    {{ $statusLabel }}
</span>
