<x-mail::message>
# New eBook order

**eBook:** {{ $order->ebook->title }}  
**Amount:** ₦{{ number_format($order->amount_kobo / 100) }}  
**Order reference:** {{ $order->reference }}  
**Status:** {{ ucfirst($order->status->value) }}

The buyer receipt and download link have been sent.
</x-mail::message>
