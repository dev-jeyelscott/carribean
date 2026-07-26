<x-mail::message>
# {{ $headline }}

Hello {{ $order->customer_name }},

{{ $summary }}

**Order:** {{ $order->order_number ?? $order->public_id }}  
**Status:** {{ $order->status->label() }}  
**Payment:** {{ $order->payment_status->label() }}  
**Fulfillment:** {{ $order->fulfillment_method->label() }}

## Order summary

@foreach ($order->items as $item)
**{{ $item->name }} × {{ $item->quantity }}**  
{{ \App\Support\Money::formatUsd($item->line_total_cents) ?? '$0.00' }}

@if (! empty($item->selected_options))
@foreach ($item->selected_options as $option)
- {{ $option['group_name'] ?? 'Option' }}: {{ $option['name'] ?? '' }}
@if (($option['additional_price_cents'] ?? 0) > 0)
  (+{{ \App\Support\Money::formatUsd((int) $option['additional_price_cents']) }})
@endif
@endforeach
@endif

@endforeach

---

Subtotal: **{{ \App\Support\Money::formatUsd($order->subtotal_cents) ?? '$0.00' }}**

@if ($order->discount_cents > 0)
Discount: **-{{ \App\Support\Money::formatUsd($order->discount_cents) ?? '$0.00' }}**
@endif

Tax: **{{ \App\Support\Money::formatUsd($order->tax_cents) ?? '$0.00' }}**

@if ($order->delivery_cents > 0)
Delivery: **{{ \App\Support\Money::formatUsd($order->delivery_cents) ?? '$0.00' }}**
@endif

Total: **{{ $order->formattedGrandTotal() }}**

@if (filled($order->customer_note))
## Your note

{{ $order->customer_note }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
