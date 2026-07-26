<x-mail::message>
# We received your reservation request

Hello {{ $reservationRequest->customer_name }},

Thank you for requesting a table at {{ $restaurantName }}.

**Preferred date:** {{ $reservationRequest->preferred_date->format('F j, Y') }}  
**Preferred time:** {{ $reservationRequest->preferred_time }}  
**Party size:** {{ $reservationRequest->guest_count }}

This message confirms only that we received your request. Your reservation is **not confirmed yet**. Our team will contact you after reviewing availability.

Thanks,<br>
{{ $restaurantName }}
</x-mail::message>
