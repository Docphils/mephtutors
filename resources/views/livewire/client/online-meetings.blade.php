<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-black text-slate-800">My <span class="text-cyan-600">Online Sessions</span></h2>
            <p class="text-xs text-slate-500 font-medium">Track scheduled, attended, and missed virtual classes.</p>
        </div>
    </x-slot>

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-bold bg-rose-50 text-rose-700 border border-rose-100">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-wrap gap-2 mb-6">
        @foreach (['scheduled' => 'Scheduled', 'attended' => 'Attended', 'missed' => 'Missed'] as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 rounded-xl text-sm font-bold {{ $tab === $key ? 'bg-cyan-600 text-white' : 'bg-slate-50 text-slate-600' }}">
                {{ $label }} ({{ $counts[$key] ?? 0 }})
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($records as $record)
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between gap-2">
                    <div>
                        <h3 class="font-black text-slate-800">{{ $record->meeting->title }}</h3>
                        <p class="text-xs text-cyan-700">{{ $record->meeting->serviceItem?->name ?? 'Online class' }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase
                        {{ $record->attendance_status === 'scheduled' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $record->attendance_status === 'attended' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $record->attendance_status === 'missed' ? 'bg-rose-100 text-rose-700' : '' }}">
                        {{ $record->attendance_status }}
                    </span>
                </div>

                <div class="mt-4 text-sm text-slate-600 space-y-1">
                    <p><strong>Tutor:</strong> {{ $record->meeting->tutor?->name ?? 'N/A' }}</p>
                    <p><strong>Start:</strong> {{ $record->meeting->starts_at?->format('M d, Y h:i A') }}</p>
                    <p><strong>End:</strong> {{ $record->meeting->ends_at?->format('M d, Y h:i A') ?? 'Open session' }}</p>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <p class="text-xs text-slate-500">{{ $record->meeting->jitsi_domain }}/{{ $record->meeting->jitsi_room }}</p>
                    <a href="{{ route('meetings.join', $record->meeting) }}"
                        class="px-3 py-2 rounded-xl bg-cyan-600 text-white text-xs font-bold hover:bg-cyan-700">
                        Join Session
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-12 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400">
                No {{ $tab }} session records found.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $records->links() }}</div>
</div>
