<main class="min-h-[70vh] bg-slate-50 flex items-center justify-center px-4 py-12 sm:py-20">
    <div class="w-full max-w-lg">
        <section class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-cyan-600 px-6 py-8 text-center text-white">
                <div class="mx-auto w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black">You're All Set!</h1>
                <p class="mt-1 text-sm text-cyan-50/90">Payment confirmed - your spot is secured</p>
            </div>

            <div class="px-6 py-6">
                <p class="text-slate-700">Hi {{ $enrollee->name }}, here's a summary of your registration:</p>

                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-sm">
                    <p><span class="font-semibold text-slate-900">Track:</span>
                        {{ $cohort->serviceItem->name ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-slate-900">Cohort:</span> {{ $cohort->name ?? 'N/A' }}
                        ({{ $cohort->code ?? 'N/A' }})</p>
                    <p><span class="font-semibold text-slate-900">Start Date:</span>
                        {{ $cohort->start_date?->format('M d, Y') ?? 'N/A' }}</p>
                </div>

                <p class="mt-4 text-sm text-slate-600">We've sent a confirmation to
                    <strong>{{ $enrollee->email }}</strong> with these details. We'll follow up shortly with what to
                    prepare before the first session.</p>

                <a href="{{ route('bootcamp') }}"
                    class="mt-6 inline-flex w-full justify-center items-center bg-cyan-600 text-white font-semibold px-4 py-2.5 rounded-lg hover:bg-cyan-700 transition">
                    Back to Bootcamp Page
                </a>
            </div>
        </section>

        <p class="text-center text-slate-500 text-xs mt-4">
            Need help? Call <span class="text-slate-700 font-semibold">(+234) 80-628-691-70</span>
        </p>
    </div>
</main>
