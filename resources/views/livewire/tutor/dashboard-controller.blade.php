<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header Section --}}
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <i class="fa-solid fa-house-laptop text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Tutor <span
                        class="text-cyan-600">Dashboard</span></h2>
                <p class="text-slate-500 text-sm font-medium">Track your earnings, lessons, and profile details at a
                    glance.</p>
            </div>
        </div>
    </x-slot>

    {{-- Offline Alert --}}
    <div wire:offline
        class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold animate-pulse">
        <i class="fa-solid fa- wifi-slash mr-2"></i> This device is currently offline.
    </div>

    {{-- Welcome & Profile Status Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-3xl p-8 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-3xl font-black text-slate-800 mb-2">Hello, {{ explode(' ', $user->name)[0] }}! 👋</h3>
                <p class="text-slate-500 font-medium mb-6">Welcome back to your teaching portal. Here is what's
                    happening today.</p>

                @if (!$tutorProfile || $incompleteTutorProfile)
                    <div class="sm:flex items-center gap-4 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                        <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600">
                            <i class="fa-solid fa-circle-exclamation text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-rose-800 font-bold text-sm">Profile Incomplete</p>
                            <p class="text-rose-600 text-xs font-medium">Complete your profile to unlock all features.
                            </p>
                        </div>
                        <a href="{{ route('tutor.tutor-profile') }}" wire:navigate
                            class="px-4 py-2 bg-white text-rose-600 border border-rose-200 rounded-xl text-xs font-black hover:bg-rose-50 transition-all">
                            Complete Now
                        </a>
                    </div>
                @else
                    @if (in_array($tutorProfile->status, ['Review', 'Pending']))
                        <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-amber-100 rounded-2xl">
                            <div
                                class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-warning text-lg"></i>
                            </div>
                            <div>
                                <p class="text-amber-800 font-bold text-sm">Profile Status: {{ $tutorProfile->status }}
                                </p>
                                <p class="text-amber-600 text-xs font-medium tracking-wide uppercase">
                                    {{ $tutorProfile->status === 'Pending' ? 'Your profile is awaiting review by MephEd admin.' : 'Your profile has been marked for review. Kindly effect recommendations for approval' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                            <div
                                class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-circle-check text-lg"></i>
                            </div>
                            <div>
                                <p class="text-emerald-800 font-bold text-sm">Profile Status:
                                    {{ $tutorProfile->status }}
                                </p>
                                <p class="text-emerald-600 text-xs font-medium tracking-wide uppercase">Your profile has
                                    been approved and you can now be matched with clients.</p>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            {{-- Decorative Element --}}
            <div class="absolute top-[-20px] right-[-20px] w-40 h-40 bg-cyan-50 rounded-full opacity-50"></div>
        </div>

        {{-- Quick Stats / Mini Calendar or Actions --}}
        <div class="bg-cyan-600 rounded-3xl p-8 text-white shadow-lg shadow-cyan-200 flex flex-col justify-center">
            <h4 class="font-bold mb-4 opacity-80 uppercase text-xs tracking-widest">Quick Actions</h4>
            <div class="space-y-3">
                <a href="{{ route('tutor.lessons') }}" wire:navigate
                    class="flex items-center justify-between p-3 bg-white/10 rounded-2xl hover:bg-white/20 transition-all group">
                    <span class="font-bold text-sm">View My Lessons</span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('tutor.payments') }}" wire:navigate
                    class="flex items-center justify-between p-3 bg-white/10 rounded-2xl hover:bg-white/20 transition-all group">
                    <span class="font-bold text-sm">Financial History</span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('tutor.institution-assignments') }}" wire:navigate
                    class="flex items-center justify-between p-3 bg-white/10 rounded-2xl hover:bg-white/20 transition-all group">
                    <span class="font-bold text-sm">Institution Assignments</span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('tutor.online-meetings') }}" wire:navigate
                    class="flex items-center justify-between p-3 bg-white/10 rounded-2xl hover:bg-white/20 transition-all group">
                    <span class="font-bold text-sm">Online Sessions</span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Financial Statistics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Pending Payments --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 mb-4">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
            <h4 class="text-slate-400 font-bold text-xs uppercase tracking-wider mb-1">Pending Payments</h4>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-black text-slate-800">{{ $pendingPayments }}</p>
                <span class="text-[10px] text-slate-400 font-medium mb-1 italic text-right">Due after lesson
                    completion</span>
            </div>
        </div>

        {{-- Earned Payments --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 mb-4">
                <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
            </div>
            <h4 class="text-slate-400 font-bold text-xs uppercase tracking-wider mb-1">Earned Payments</h4>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-black text-slate-800">{{ $earnedPayments }}</p>
                <span class="text-[10px] text-slate-400 font-medium mb-1 italic text-right">Disbursed within
                    24hrs</span>
            </div>
        </div>

        {{-- Completed Payments --}}
        <div
            class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all border-b-4 border-b-cyan-500">
            <div class="w-12 h-12 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600 mb-4">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
            <h4 class="text-slate-400 font-bold text-xs uppercase tracking-wider mb-1">Total Paid Out</h4>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-black text-slate-800">{{ $completedPayments }}</p>
                <span class="text-[10px] text-slate-400 font-medium mb-1 italic">Successful transfers</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                <i class="fa-solid fa-video text-xl"></i>
            </div>
            <h4 class="text-slate-400 font-bold text-xs uppercase tracking-wider mb-1">Online Sessions</h4>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-black text-slate-800">{{ $meetingStats['scheduled'] }}</p>
                <span class="text-[10px] text-slate-400 font-medium mb-1 italic">
                    {{ $meetingStats['attended'] }} attended | {{ $meetingStats['missed'] }} missed
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-black text-slate-800">Institution Assignments</h4>
            <span class="text-xs font-bold text-slate-400 uppercase">Recent</span>
        </div>
        <div class="p-6 space-y-3">
            @forelse ($institutionAssignments as $assignment)
                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50">
                    <p class="text-sm font-black text-slate-800">{{ $assignment->crm?->institution_name }}</p>
                    <p class="text-xs text-cyan-700 font-bold">{{ $assignment->crm?->serviceItem?->name }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] uppercase font-black text-slate-500">
                            {{ $assignment->role }} | {{ $assignment->status }}
                        </span>
                        <span
                            class="text-[10px] text-slate-400 font-bold">{{ $assignment->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No institutional assignments yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-black text-slate-800">Upcoming Online Sessions</h4>
            <a href="{{ route('tutor.online-meetings') }}" wire:navigate class="text-xs font-bold text-cyan-600">Open</a>
        </div>
        <div class="p-6 space-y-3">
            @forelse ($upcomingMeetings as $meetingRecord)
                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50">
                    <p class="text-sm font-black text-slate-800">{{ $meetingRecord->meeting?->title }}</p>
                    <p class="text-xs text-cyan-700">{{ $meetingRecord->meeting?->starts_at?->format('M d, h:i A') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No upcoming online sessions.</p>
            @endforelse
        </div>
    </div>
</div>
