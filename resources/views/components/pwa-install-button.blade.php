<button type="button" data-pwa-install data-install-label="Install App" data-install-ios-label="How to Install"
    data-install-busy-label="Preparing..." aria-hidden="true"
    {{ $attributes->merge(['class' => 'hidden inline-flex items-center gap-2 rounded-full bg-cyan-700 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-cyan-900/40 transition hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-200 focus:ring-offset-2']) }}>
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 3v11"></path>
        <path d="M8 10l4 4 4-4"></path>
        <path d="M5 16v3h14v-3"></path>
    </svg>
    <span data-pwa-install-text>Install App</span>
</button>
