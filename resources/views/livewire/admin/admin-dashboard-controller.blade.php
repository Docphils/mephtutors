<div class="min-h-screen bg-slate-50/50">
    <div wire:offline class="fixed top-6 right-6 z-50">
        <div class="flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-full shadow-2xl animate-pulse">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a5 5 0 010-7.072M4.929 19.071a9 9 0 010-12.728m0 0l2.829 2.829m-2.829-2.829L3 3">
                </path>
            </svg>
            <span class="text-xs font-bold uppercase tracking-wider">System Offline</span>
        </div>
    </div>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-black text-3xl text-slate-800 tracking-tight">
                    Dashboard<span class="text-cyan-600">.</span>
                </h2>
                <p class="text-slate-500 text-sm font-medium">Platform overview and analytics</p>
            </div>
            <div class="sm:hidden block">
                <livewire:user-profile lazy="on-load" />
            </div>
        </div>
    </x-slot>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-cyan-100 text-cyan-800 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="relative overflow-hidden bg-cyan-100 border border-cyan-500 rounded-xl p-8 mb-10 shadow-sm">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800">
                            Welcome back, <span class="text-cyan-600">{{ explode(' ', $user->name)[0] }}</span>! 👋
                        </h3>
                        <p class="text-slate-500 mt-1">Everything looks great. You have <span
                                class="text-cyan-600 font-bold">{{ $stats['new_crm'] }}</span> new requests to review.
                        </p>
                    </div>

                    @if (!$userProfile)
                        <div class="flex items-center gap-4 bg-red-50 border border-red-100 p-4 rounded-2xl">
                            <div class="bg-red-500 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-red-800">Profile Incomplete</h4>
                                <p class="text-xs text-red-600">Please update your details to ensure full functionality.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-cyan-50 rounded-full blur-3xl opacity-50"></div>
            </div>

            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-6 pl-2">System Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-100 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-cyan-50 text-cyan-600 p-3 rounded-2xl group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-400">TOTAL</span>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Registered Users</h4>
                        <div class="flex items-baseline gap-2">
                            <p class="text-4xl font-black text-slate-800 mt-1">
                                {{ number_format($stats['total_users']) }}</p>
                            <span class="text-emerald-500 text-xs font-bold">↑ 12%</span>
                        </div>
                    </div>
                </div>

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-100 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-blue-50 text-blue-600 p-3 rounded-2xl group-hover:bg-blue-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-400">PENDING</span>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Tutor Requests</h4>
                        <p class="text-4xl font-black text-slate-800 mt-1">{{ number_format($stats['tutor_requests']) }}
                        </p>
                    </div>
                </div>

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-100 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-emerald-50 text-emerald-600 p-3 rounded-2xl group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-400">LIVE</span>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Active Bookings</h4>
                        <p class="text-4xl font-black text-slate-800 mt-1">
                            {{ number_format($stats['active_bookings']) }}</p>
                    </div>
                </div>

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-indigo-50 text-indigo-600 p-3 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Completed Bookings</h4>
                        <p class="text-4xl font-black text-slate-800 mt-1">
                            {{ number_format($stats['completed_bookings']) }}</p>
                    </div>
                </div>

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-cyan-50 text-cyan-600 p-3 rounded-2xl group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Payments Earned</h4>
                        <p class="text-4xl font-black text-slate-800 mt-1">
                            {{ number_format($stats['earned_payments']) }}</p>
                    </div>
                </div>

                <div
                    class="group bg-white p-1 rounded-[2rem] border border-slate-200 hover:border-cyan-300 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="bg-orange-50 text-orange-600 p-3 rounded-2xl group-hover:bg-orange-600 group-hover:text-white transition-colors duration-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-slate-500 font-semibold text-sm">Coding/Music Requests</h4>
                        <p class="text-4xl font-black text-slate-800 mt-1">{{ number_format($stats['new_crm']) }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
