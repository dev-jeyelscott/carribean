<x-mail::message>
# {{ $headline }}

{{ $summary }}

**Order:** {{ $order->order_number ?? $order->public_id }}  
**Customer:** {{ $order->customer_name }}  
**Email:** {{ $order->customer_email }}  
**Phone:** {{ $order->customer_phone }}  
**Status:** {{ $order->status->label() }}  
**Payment:** {{ $order->payment_status->label() }}  
**Payment method:** {{ $order->payment_method->label() }}  
**Fulfillment:** {{ $order->fulfillment_method->label() }}  
**Total:** {{ $order->formattedGrandTotal() }}

## Items

@foreach ($order->items as $item)
- {{ $item->quantity }} × {{ $item->name }}
  — {{ \App\Support\Money::formatUsd($item->line_total_cents) ?? '$0.00' }}

@if (! empty($item->selected_options))
@foreach ($item->selected_options as $option)
  - {{ $option['group_name'] ?? 'Option' }}: {{ $option['name'] ?? '' }}
@endforeach
@endif
@endforeach

@if (filled($order->customer_note))
## Customer note

{{ $order->customer_note }}
@endif

Review this order in the Filament administration panel.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
