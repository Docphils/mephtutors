<main class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-7 text-center shadow-sm sm:p-10">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-700">Payment confirmed</p>
        <h1 class="mt-3 text-3xl font-black text-[#0B2A47]">Thank you, {{ $order->buyer_name }}</h1>
        <p class="mt-3 text-slate-600">Your order for <strong>{{ $order->ebook->title }}</strong> is confirmed.</p>
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="mt-7 inline-flex rounded-xl bg-[#0B2A47] px-6 py-3 font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:ring-offset-2">Download your eBook</a>
            <p class="mt-4 text-sm text-slate-500">We also sent the download link to {{ $order->buyer_email }}. It expires after {{ config('ebooks.fulfilment.download_ttl_hours') }} hours and allows {{ config('ebooks.fulfilment.max_downloads') }} downloads.</p>
        @else
            @if(!$order->fulfilled_at)
                <p role="status" class="mt-6 rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm text-cyan-950">We are preparing your stamped eBook. We will email the download link to {{ $order->buyer_email }} when it is ready.</p>
            @else
                <p class="mt-6 text-sm text-slate-600">Your download link expired or reached its download limit. We can email a fresh link if the purchase email matches.</p>
                <form method="POST" action="{{ $resendUrl }}" class="mx-auto mt-5 max-w-md space-y-3 text-left">
                    @csrf
                    <label class="block text-sm font-semibold text-slate-700">Purchase email<input type="email" name="email" autocomplete="email" maxlength="254" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-200"></label>
                    <div class="absolute -left-[10000px]" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <button type="submit" class="w-full rounded-xl bg-[#0B2A47] px-5 py-3 font-bold text-white hover:bg-slate-800">Email a fresh link</button>
                </form>
                @if(session('resend_message'))<p role="status" class="mt-4 text-sm text-slate-600">{{ session('resend_message') }}</p>@endif
            @endif
        @endif
        <p class="mt-6 text-xs text-slate-500">Order reference: {{ $order->reference }}</p>
        <a href="{{ route('welcome') }}" class="mt-5 inline-block text-sm font-bold text-cyan-800 hover:underline">Return to MephEd</a>
    </section>
</main>
