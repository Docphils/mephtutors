@php
    $userRole = auth()->user()?->role;
    $interventionNavUrl = match ($userRole) {
        'client' => route('programmes.enquiry'),
        'admin' => route('admin.dashboard'),
        'tutor' => route('tutor.dashboard'),
        default => route('programmes.enquiry'),
    };

    $primaryLinks = [
        ['label' => 'Home', 'url' => route('welcome'), 'active' => request()->routeIs('welcome')],
        ['label' => 'Services', 'url' => route('services'), 'active' => request()->routeIs('services', 'services.show')],
        ['label' => 'Products', 'url' => route('products.index'), 'active' => request()->routeIs('products.index')],
        ['label' => 'Interventions', 'url' => $interventionNavUrl, 'active' => request()->is('programmes*') || request()->routeIs('client.interventions.create')],
        ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
    ];

    $dashboard = match ($userRole) {
        'admin' => ['label' => 'Admin Dashboard', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
        'client' => ['label' => 'Client Dashboard', 'url' => route('client.dashboard'), 'active' => request()->routeIs('client.dashboard')],
        'tutor' => ['label' => 'Tutor Dashboard', 'url' => route('tutor.dashboard'), 'active' => request()->routeIs('tutor.dashboard')],
        default => null,
    };
@endphp

<header x-data="{ open: false }" @keydown.escape.window="open = false"
    class="sticky top-0 z-40 bg-gradient-to-r from-cyan-800 via-cyan-900 to-slate-950 text-white shadow-lg shadow-slate-950/10">
    <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">
        <a wire:navigate href="{{ route('welcome') }}" class="flex shrink-0 items-center rounded focus:outline-none focus:ring-2 focus:ring-cyan-300" aria-label="MephEd home">
            <img src="{{ asset('images/MephEd.png') }}" alt="MephEd" width="128" height="36" fetchpriority="high" class="h-8 w-28 object-contain sm:w-32">
        </a>

        <nav aria-label="Main navigation" class="hidden min-w-0 flex-1 items-center justify-center gap-1 lg:flex xl:gap-2">
            @foreach ($primaryLinks as $link)
                <a wire:navigate href="{{ $link['url'] }}" @if($link['active']) aria-current="page" @endif
                    class="whitespace-nowrap rounded-lg px-2.5 py-2 text-sm font-semibold transition hover:bg-white/10 hover:text-cyan-100 focus:outline-none focus:ring-2 focus:ring-cyan-300 xl:px-3 {{ $link['active'] ? 'bg-white/10 text-white' : 'text-cyan-50/90' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden shrink-0 items-center gap-2 lg:flex">
            @auth
                @if ($dashboard)
                    <a wire:navigate href="{{ $dashboard['url'] }}" @if($dashboard['active']) aria-current="page" @endif
                        class="rounded-lg px-3 py-2 text-sm font-semibold text-cyan-50 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-300">
                        {{ $dashboard['label'] }}
                    </a>
                @endif
            @else
                @if (Route::has('login'))
                    <a wire:navigate href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-cyan-50 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-300">Log in</a>
                @endif
                @if (Route::has('register'))
                    <a wire:navigate href="{{ route('register') }}" class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-white">Sign up</a>
                @endif
            @endauth
        </div>

        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-main-navigation"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-300 lg:hidden">
            <span class="sr-only" x-text="open ? 'Close navigation menu' : 'Open navigation menu'"></span>
            <svg x-show="!open" aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" /></svg>
            <svg x-cloak x-show="open" aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg>
        </button>
    </div>

    <div id="mobile-main-navigation" x-cloak x-show="open" x-transition.origin.top @click="if ($event.target.closest('a')) open = false"
        class="absolute inset-x-0 top-full border-t border-white/10 bg-slate-950/95 px-4 pb-5 pt-3 text-white shadow-xl backdrop-blur lg:hidden sm:px-6">
        <nav aria-label="Mobile main navigation" class="mx-auto grid max-w-7xl gap-1">
            @foreach ($primaryLinks as $link)
                <a wire:navigate href="{{ $link['url'] }}" @if($link['active']) aria-current="page" @endif
                    class="rounded-xl px-4 py-3 text-sm font-semibold transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-300 {{ $link['active'] ? 'bg-cyan-400/15 text-cyan-100' : 'text-slate-200' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="mt-2 border-t border-white/10 pt-3">
                @auth
                    @if ($dashboard)
                        <a wire:navigate href="{{ $dashboard['url'] }}" @if($dashboard['active']) aria-current="page" @endif
                            class="block rounded-xl px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-white/10">{{ $dashboard['label'] }}</a>
                    @endif
                @else
                    <div class="flex gap-2">
                        @if (Route::has('login'))
                            <a wire:navigate href="{{ route('login') }}" class="flex-1 rounded-xl border border-white/15 px-4 py-3 text-center text-sm font-semibold text-white">Log in</a>
                        @endif
                        @if (Route::has('register'))
                            <a wire:navigate href="{{ route('register') }}" class="flex-1 rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-bold text-slate-950">Sign up</a>
                        @endif
                    </div>
                @endauth
            </div>
        </nav>
    </div>
</header>
