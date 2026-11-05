<main class="mx-auto max-w-2xl px-4 py-16 sm:px-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-7 text-center shadow-sm sm:p-10">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-700">Download link unavailable</p>
        <h1 class="mt-3 text-3xl font-black text-[#0B2A47]">Request a fresh link</h1>
        <p class="mt-3 text-slate-600">This link has expired or reached its download limit. Enter the email used for the purchase and we will send a new link if it matches the order.</p>
        @if(isset($token))
            <form method="POST" action="{{ route('ebooks.download.resend', ['token' => $token]) }}" class="mx-auto mt-7 max-w-md space-y-4 text-left">
                @csrf
                <label class="block text-sm font-semibold text-slate-700">Purchase email<input type="email" name="email" autocomplete="email" maxlength="254" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-200"></label>
                <div class="absolute -left-[10000px]" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <button type="submit" class="w-full rounded-xl bg-[#0B2A47] px-5 py-3 font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-600 focus:ring-offset-2">Email me a new link</button>
            </form>
            @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
            @if(session('resend_message'))<p role="status" class="mt-4 text-sm text-slate-600">{{ session('resend_message') }}</p>@endif
        @else
            <p class="mt-5 text-sm text-slate-500">Use the link in your purchase email or contact support for help.</p>
        @endif
        <a href="{{ route('welcome') }}" class="mt-6 inline-block text-sm font-bold text-cyan-800 hover:underline">Return to MephEd</a>
    </section>
</main>
