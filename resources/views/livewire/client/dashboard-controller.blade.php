<div class="min-h-screen bg-slate-50/50 p-4 lg:p-8">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">
                    Executive <span class="text-cyan-600">Dashboard</span>
                </h2>
                <p class="text-slate-500 font-bold text-sm">Welcome back, {{ $user->name }}. Here is your academic
                    overview.
                </p>
            </div>

            @if (!$userProfile || $incompleteProfile)
                <div class="flex items-center gap-3 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm">
                    <div class="bg-red-100 p-2 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Profile Incomplete!</p>
                        <a wire:navigate href="{{ route('userProfile') }}"
                            class="text-[10px] text-cyan-600 hover:underline font-bold uppercase tracking-wider">Update
                            Now</a>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-cyan-50 p-3 rounded-2xl">
                    <i class="fas fa-chalkboard-teacher text-cyan-600 text-xl"></i>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg">ACTIVE</span>
            </div>
            <h4 class="text-slate-500 text-sm font-bold uppercase tracking-widest">Active Lessons</h4>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['activeLessons'] }}</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-50 p-3 rounded-2xl">
                    <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400">{{ $stats['matchRate'] }}% Match Rate</p>
                </div>
            </div>
            <h4 class="text-slate-500 text-sm font-bold uppercase tracking-widest">Tutor Requests</h4>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['totalRequests'] }}</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-50 p-3 rounded-2xl">
                    <i class="fas fa-school text-purple-600 text-xl"></i>
                </div>
            </div>
            <h4 class="text-slate-500 text-sm font-bold uppercase tracking-widest">Institutions</h4>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['activeInstitutions'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-cyan-600 to-cyan-800 p-6 rounded-3xl shadow-lg shadow-cyan-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 p-3 rounded-2xl">
                    <i class="fas fa-wallet text-white text-xl"></i>
                </div>
            </div>
            <h4 class="text-cyan-100 text-sm font-bold uppercase tracking-widest">Total Investment</h4>
            <p class="text-3xl font-black text-white mt-1">₦{{ number_format($stats['totalInvestment'], 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 tracking-tight">Recent Bookings</h3>
                <a wire:navigate href="{{ route('client.lessons') }}"
                    class="text-xs font-bold text-cyan-600 hover:text-cyan-700">View
                    All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tutor
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Service</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($recentBookings as $booking)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 font-bold text-xs">
                                            {{ substr($booking->tutor->name, 0, 1) }}
                                        </div>
                                        <span
                                            class="text-sm font-bold text-slate-700">{{ $booking->tutor->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                    {{ $booking->serviceItem->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                    {{ $booking->status == 'Active' ? 'bg-cyan-50 text-cyan-600' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-black text-slate-700 text-right">
                                    ₦{{ number_format($booking->amount, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <h3 class="font-black text-slate-800 tracking-tight mb-6 text-center">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a wire:navigate href="{{ route('client.tutorRequests.manager') }}"
                        class="flex flex-col items-center p-4 rounded-2xl bg-slate-50 hover:bg-cyan-50 group transition-all">
                        <i class="fas fa-plus text-cyan-600 mb-2 group-hover:scale-110 transition"></i>
                        <span class="text-[10px] font-black text-slate-600 uppercase">New Request</span>
                    </a>
                    <a wire:navigate href="{{ route('client.crm.manager') }}"
                        class="flex flex-col items-center p-4 rounded-2xl bg-slate-50 hover:bg-cyan-50 group transition-all">
                        <i class="fas fa-building text-cyan-600 mb-2 group-hover:scale-110 transition"></i>
                        <span class="text-[10px] font-black text-slate-600 uppercase">Institution</span>
                    </a>
                </div>
            </div>

            <div class="bg-slate-900 p-8 rounded-3xl text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-cyan-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Member Since</p>
                    <p class="text-xl font-bold mb-6">{{ $user->created_at->format('M Y') }}</p>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Total Sessions</span>
                            <span class="font-bold">{{ $stats['completedLessons'] }}</span>
                        </div>
                        <div class="w-full bg-white/10 h-1.5 rounded-full">
                            <div class="bg-cyan-500 h-1.5 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                <i class="fas fa-graduation-cap absolute -bottom-4 -right-4 text-white/5 text-8xl rotate-12"></i>
            </div>
        </div>
    </div>
</div>
