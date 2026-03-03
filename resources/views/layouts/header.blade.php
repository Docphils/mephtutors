<!-- Header Section -->
<header x-data="{ open: false }" class="bg-gradient-to-r from-cyan-500 to-cyan-900 text-white py-6 shadow-lg">
    <div class="container mx-auto grid grid-cols-3 items-center px-6">
        <div class="mr-2">
            <img src="/images/MephEd.png" alt="Logo Image" class="object-cover h-6 sm:h-8 w-24 sm:w-32">
        </div>
        <div class="hidden sm:flex justify-between col-span-2">
            <nav class="md:space-x-4 text-lg">
                <a href="{{ url('/') }}" wire:navigate
                    class="hover:text-gray-200 transition {{ request()->is('/') ? 'active' : '' }}">Home</a>
                <a href="{{ url('/services') }}" wire:navigate
                    class="hover:text-gray-200 transition {{ request()->is('services') ? 'active' : '' }}">Our
                    Services</a>
                <a href="{{ url('/about') }}" wire:navigate
                    class="hover:text-gray-200 transition {{ request()->is('about') ? 'active' : '' }}">About</a>
                <a href="{{ url('/contact') }}" wire:navigate
                    class="hover:text-gray-200 transition {{ request()->is('contact') ? 'active' : '' }}">Contact</a>
            </nav>
            <div class="text-right md:space-x-4 text-lg">
                @if (Route::has('login'))
                    <div class="">
                        @auth
                            @if (Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" wire:navigate
                                    class="hover:text-gray-200 transition {{ request()->is('admin.dashboard') ? 'active' : '' }}">
                                    {{ __('Admin Dashboard') }}
                                </a>
                            @elseif(Auth::user()->role === 'client')
                                <a href="{{ route('client.dashboard') }}" wire:navigate
                                    class="hover:text-gray-200 transition {{ request()->is('client.dashboard') ? 'active' : '' }}">
                                    {{ __('Client Dashboard') }}
                                </a>
                            @elseif(Auth::user()->role === 'tutor')
                                <a href="{{ route('tutor.dashboard') }}" wire:navigate
                                    class="hover:text-gray-200 transition {{ request()->is('tutor.dashboard') ? 'active' : '' }}">
                                    {{ __('Tutor Dashboard') }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="hover:text-gray-200">Login</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" wire:navigate
                                    class="bg-cyan-800 hover:bg-cyan-100 rounded-lg p-1 hover:text-gray-600 transition">Sign
                                    Up</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-start col-span-2 justify-end ">

            <!--Mobile Nav-->
            <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden text-right">
                <nav class="grid text-lg">
                    <a href="{{ url('/') }}" wire:navigate
                        class="hover:text-gray-200 transition {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    <a href="{{ url('/services') }}" wire:navigate
                        class="hover:text-gray-200 transition {{ request()->is('services') ? 'active' : '' }}">Our
                        Services</a>
                    <a href="{{ url('/about') }}" wire:navigate
                        class="hover:text-gray-200 transition {{ request()->is('about') ? 'active' : '' }}">About</a>
                    <a href="{{ url('/contact') }}" wire:navigate
                        class="hover:text-gray-200 transition {{ request()->is('contact') ? 'active' : '' }}">Contact</a>
                </nav>
                <div class="text-right grid text-lg">
                    @if (Route::has('login'))
                        <div class="">
                            @auth
                                @if (Auth::user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" wire:navigate
                                        class="hover:text-gray-200 transition {{ request()->is('admin.dashboard') ? 'active' : '' }}">
                                        {{ __('Admin Dashboard') }}
                                    </a>
                                @elseif(Auth::user()->role === 'client')
                                    <a href="{{ route('client.dashboard') }}" wire:navigate
                                        class="hover:text-gray-200 transition {{ request()->is('client.dashboard') ? 'active' : '' }}">
                                        {{ __('Client Dashboard') }}
                                    </a>
                                @elseif(Auth::user()->role === 'tutor')
                                    <a href="{{ route('tutor.dashboard') }}" wire:navigate
                                        class="hover:text-gray-200 transition {{ request()->is('tutor.dashboard') ? 'active' : '' }}">
                                        {{ __('Tutor Dashboard') }}
                                    </a>
                                @endif
                            @else
                                <div class="grid text-end mx-0">
                                    <a href="{{ route('login') }}" wire:navigate class="hover:text-gray-200">Login</a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" wire:navigate
                                            class="bg-cyan-800 hover:bg-cyan-100 rounded-lg p-1 hover:text-gray-600 transition">Sign
                                            Up</a>
                                    @endif
                                </div>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-cyan-100 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100  focus:text-gray-500  transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

</header>
