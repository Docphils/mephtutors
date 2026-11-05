<x-mail::message>
# Your eBook is ready

Hello {{ $order->buyer_name }},

Your payment for **{{ $order->ebook->title }}** has been confirmed.

<x-mail::button :url="$downloadUrl">
Download your eBook
</x-mail::button>

This link expires after {{ config('ebooks.fulfilment.download_ttl_hours') }} hours and allows {{ config('ebooks.fulfilment.max_downloads') }} downloads. Keep this email private because the link gives access to your file.

Order reference: {{ $order->reference }}

If you need help, contact [{{ config('mail.from.address') }}](mailto:{{ config('mail.from.address') }}).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
