<x-mail::message>
# We received your message

Hello {{ $contactInquiry->customer_name }},

Thank you for contacting {{ $restaurantName }}. Our team has received your message and will review it as soon as possible.

@if ($contactInquiry->subject)
**Subject:** {{ $contactInquiry->subject }}
@endif

No further action is needed from you right now.

Thanks,<br>
{{ $restaurantName }}
</x-mail::message>
