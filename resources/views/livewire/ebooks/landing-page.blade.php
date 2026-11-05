<article class="mx-auto max-w-6xl space-y-10 px-4 py-10 text-slate-800 sm:px-6">
    <section class="grid items-center gap-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-[minmax(0,1fr)_minmax(250px,0.7fr)] md:p-10">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-700">MephEd eBooks</p>
            <h1 class="mt-3 text-4xl font-black leading-tight text-[#0B2A47] md:text-5xl">{{ $ebook->title }}</h1>
            @if($ebook->subtitle)<p class="mt-4 text-lg leading-relaxed text-slate-600">{{ $ebook->subtitle }}</p>@endif
            @if($ebook->description)<p class="mt-4 whitespace-pre-line leading-7 text-slate-700">{{ $ebook->description }}</p>@endif
            <p class="mt-5 text-sm text-slate-600">By <strong>{{ $ebook->author_name }}</strong> · Published by <strong>{{ $ebook->publisher_name }}</strong></p>
            <p class="mt-6 text-2xl font-black text-[#0B2A47]">₦{{ number_format($ebook->price_kobo / 100) }}</p>
            @if(session('ebook_payment_success'))<p role="status" class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">{{ session('ebook_payment_success') }}</p>@endif
            @if(session('ebook_payment_error'))<p role="alert" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm font-semibold text-rose-800">{{ session('ebook_payment_error') }}</p>@endif
            @if($ebookSalesEnabled)
            <form wire:submit="beginCheckout" class="mt-6 max-w-md space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="font-bold text-[#0B2A47]">Buy this eBook <span class="font-normal text-slate-500">· no account needed</span></p>
                <label class="block text-sm font-semibold text-slate-700">Your name<input wire:model="buyerName" autocomplete="name" maxlength="160" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-200">@error('buyerName')<span class="text-xs text-rose-700">{{ $message }}</span>@enderror</label>
                <label class="block text-sm font-semibold text-slate-700">Email for your receipt and book<input type="email" wire:model="buyerEmail" autocomplete="email" maxlength="254" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-200">@error('buyerEmail')<span class="text-xs text-rose-700">{{ $message }}</span>@enderror</label>
                <label class="block text-sm font-semibold text-slate-700">Phone <span class="font-normal text-slate-500">(optional)</span><input type="tel" wire:model="buyerPhone" autocomplete="tel" maxlength="30" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-200">@error('buyerPhone')<span class="text-xs text-rose-700">{{ $message }}</span>@enderror</label>
                <div class="absolute -left-[10000px]" aria-hidden="true"><label>Website<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label></div>
                @error('checkout')<p role="alert" class="text-sm text-rose-700">{{ $message }}</p>@enderror
                <p wire:loading wire:target="beginCheckout" role="status" class="text-sm text-slate-600">Connecting to Paystack. This can take up to 15 seconds. You will only be charged after authorizing payment.</p>
                <button type="submit" wire:loading.attr="disabled" wire:target="beginCheckout" class="w-full rounded-xl bg-[#0B2A47] px-5 py-3 font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:ring-offset-2 disabled:opacity-60"> <span wire:loading.remove wire:target="beginCheckout">Continue to secure payment</span><span wire:loading wire:target="beginCheckout">Preparing secure checkout…</span></button>
                @if($checkoutAuthorizationUrl)<a href="{{ $checkoutAuthorizationUrl }}" class="block text-center text-sm font-semibold text-cyan-800 underline">Open secure checkout</a>@endif
                <p class="text-xs text-slate-500">Payment is verified securely before an order is confirmed. We use your details to process and deliver this purchase.</p>
                <p class="text-xs text-slate-500">By continuing, you acknowledge the <a class="underline" href="{{ route('ebooks.policy', ['policy' => 'terms']) }}">terms</a>, <a class="underline" href="{{ route('ebooks.policy', ['policy' => 'refund']) }}">refund policy</a>, and <a class="underline" href="{{ route('ebooks.policy', ['policy' => 'privacy']) }}">privacy notice</a>.</p>
            </form>
            @else
                <p role="status" class="mt-6 max-w-md rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">Purchases are not open yet. Please check back later or contact MephEd.</p>
            @endif
            @if($ebook->free_chapter_content || $ebook->free_chapter_path)
                <a href="{{ route('ebooks.free-chapter', ['slug' => $ebook->slug]) }}" class="mt-5 inline-flex rounded-xl border border-[#17C3D6] px-5 py-3 font-bold text-[#0B2A47] hover:bg-cyan-50 focus:outline-none focus:ring-2 focus:ring-cyan-600">Read a free chapter</a>
            @endif
            @if($ebook->cheat_sheet_path)
                <a href="{{ route('ebooks.cheat-sheet', ['slug' => $ebook->slug]) }}" class="ml-2 mt-5 inline-flex rounded-xl border border-slate-300 px-5 py-3 font-bold text-[#0B2A47] hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-600">Download the free cheat sheet</a>
            @endif
        </div>
        @if($ebook->cover_path)
            <img src="{{ asset('storage/'.$ebook->cover_path) }}" alt="Cover of {{ $ebook->title }}" width="640" height="800" fetchpriority="high" class="mx-auto max-h-[28rem] w-auto rounded-xl object-contain shadow-lg">
        @endif
    </section>
    @if($ebook->audience || $ebook->contents)
        <section class="grid gap-6 md:grid-cols-2">
            @if($ebook->audience)<div class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="text-xl font-bold text-[#0B2A47]">Who it is for</h2><p class="mt-3 whitespace-pre-line leading-7">{{ $ebook->audience }}</p></div>@endif
            @if($ebook->contents)<div class="rounded-2xl border border-slate-200 bg-white p-6 md:col-span-2"><h2 class="text-xl font-bold text-[#0B2A47]">What is inside</h2><p class="mt-3 whitespace-pre-line leading-7">{{ $ebook->contents }}</p></div>@endif
        </section>
    @endif
    @if($ebook->faq)
        <section class="rounded-2xl border border-slate-200 bg-white p-6"><h2 class="text-2xl font-black text-[#0B2A47]">Frequently asked questions</h2><div class="mt-4 space-y-4">@foreach($ebook->faq as $item)<details class="border-b border-slate-200 pb-4"><summary class="cursor-pointer font-bold">{{ $item['question'] }}</summary><p class="mt-2 whitespace-pre-line text-slate-700">{{ $item['answer'] }}</p></details>@endforeach</div></section>
    @endif
    <p class="text-center text-sm text-slate-600">Questions? <a class="font-semibold text-cyan-800 underline" href="https://wa.me/2347062599737">Message MephEd on WhatsApp</a></p>
</article>

@script
<script>
    $wire.on('ebook-checkout-initialized', async (event) => {
        const { accessCode, authorizationUrl } = event.detail || {};

        try {
            if (!window.PaystackPop) {
                await Promise.race([new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://js.paystack.co/v2/inline.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                }), new Promise((_, reject) => setTimeout(() => reject(new Error('Paystack script timed out')), 8000))]);
            }

            if (!accessCode) throw new Error('Paystack did not return an access code.');

            new PaystackPop().resumeTransaction(accessCode);
        } catch (error) {
            console.error('Paystack checkout could not be opened.', error);
            if (authorizationUrl) window.location.assign(authorizationUrl);
        }
    });
</script>
@endscript
