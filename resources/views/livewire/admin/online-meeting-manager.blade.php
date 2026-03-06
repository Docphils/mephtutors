<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800">Online Class <span class="text-cyan-600">Scheduler</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium">Manage virtual sessions separately from bookings.</p>
            </div>
            <button x-on:click="$dispatch('create-online-meeting')"
                class="bg-cyan-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-700">
                New Session
            </button>
        </div>
    </x-slot>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 rounded-xl text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm mb-6 flex flex-col sm:flex-row gap-3">
        <input wire:model.live.debounce.300ms="search" placeholder="Search by title, room, client or tutor..."
            class="flex-1 rounded-xl border-slate-200 text-sm" />
        <select wire:model.live="status" class="rounded-xl border-slate-200 text-sm">
            <option value="all">All statuses</option>
            <option value="scheduled">Scheduled</option>
            <option value="live">Live</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-cyan-700 text-white text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4">Title / Room</th>
                        <th class="px-5 py-4">Participants</th>
                        <th class="px-5 py-4">Schedule</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($meetings as $meeting)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800">{{ $meeting->title }}</p>
                                <p class="text-xs text-cyan-700">{{ $meeting->jitsi_domain }}/{{ $meeting->jitsi_room }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                <p>Client: {{ $meeting->client?->name ?? 'N/A' }}</p>
                                <p>Tutor: {{ $meeting->tutor?->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                <p>{{ $meeting->starts_at?->format('M d, Y h:i A') }}</p>
                                <p class="text-xs text-slate-400">to
                                    {{ $meeting->ends_at?->format('M d, Y h:i A') ?? 'Open' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    {{ $meeting->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $meeting->status === 'live' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $meeting->status === 'completed' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                    {{ $meeting->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ $meeting->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="openDetails({{ $meeting->id }})"
                                        class="text-slate-500 hover:text-cyan-600"><i
                                            class="fa-solid fa-eye"></i></button>
                                    <button wire:click="editMeeting({{ $meeting->id }})"
                                        class="text-slate-500 hover:text-amber-600"><i
                                            class="fa-solid fa-pen"></i></button>
                                    <a href="{{ route('meetings.join', $meeting) }}"
                                        class="text-slate-500 hover:text-emerald-600"><i
                                            class="fa-solid fa-video"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">No online sessions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">{{ $meetings->links() }}</div>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60" wire:click="$set('showForm', false)"></div>
            <div class="relative bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6">
                <h3 class="text-xl font-black text-slate-800 mb-4">{{ $editingId ? 'Edit' : 'Create' }} Online Session
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500">Related Booking</label>
                        <select wire:model.live="booking_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">Optional</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}">#{{ $booking->id }} -
                                    {{ $booking->client?->name }} / {{ $booking->tutor?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Title</label>
                        <input type="text" wire:model="title" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('title')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Client</label>
                        <select wire:model="client_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">None</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Tutor</label>
                        <select wire:model="tutor_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">None</option>
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }}</option>
                            @endforeach
                        </select>
                        @error('tutor_id')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Jitsi Domain</label>
                        <input type="text" wire:model="jitsi_domain"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('jitsi_domain')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Room ID (auto)</label>
                        <input type="text" wire:model="jitsi_room"
                            class="w-full rounded-xl border-slate-200 text-sm bg-slate-50" readonly />
                        <p class="text-[10px] text-slate-400 mt-1">Pattern: MephEd-&lt;booking_id&gt;/&lt;session_count&gt;</p>
                        @error('jitsi_room')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Start</label>
                        <input type="datetime-local" wire:model="starts_at"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('starts_at')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">End</label>
                        <input type="datetime-local" wire:model="ends_at"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('ends_at')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Status</label>
                        <select wire:model="meeting_status" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="scheduled">Scheduled</option>
                            <option value="live">Live</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model="recording_enabled"
                                class="rounded border-slate-300 text-cyan-600">
                            Enable recording toolbar
                        </label>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Tutor Privilege</label>
                        <select wire:model="tutor_privilege" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="participant">Participant</option>
                            <option value="moderator">Moderator</option>
                        </select>
                        @error('tutor_privilege')
                            <p class="text-[10px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-end text-xs text-slate-500">
                        Admin privilege is fixed to <span class="font-bold text-slate-700 ml-1">Moderator</span>.
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-500">Description</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-xl border-slate-200 text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showForm', false)"
                        class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">Cancel</button>
                    <button wire:click="saveMeeting"
                        class="px-4 py-2 rounded-xl bg-cyan-600 text-white font-bold">Save Session</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDetails && $selectedMeeting)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60" wire:click="closeDetails"></div>
            <div class="relative bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6">
                <div class="flex justify-between items-start gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">{{ $selectedMeeting->title }}</h3>
                        <p class="text-xs text-slate-500">
                            {{ $selectedMeeting->jitsi_domain }}/{{ $selectedMeeting->jitsi_room }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('meetings.join', $selectedMeeting) }}"
                            class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">Open Room</a>
                        <button wire:click="deleteMeeting({{ $selectedMeeting->id }})"
                            class="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold">Delete</button>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-xs uppercase font-bold text-slate-400 mb-1">Client</p>
                        <p class="font-semibold text-slate-700">{{ $selectedMeeting->client?->name ?? 'N/A' }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-xs uppercase font-bold text-slate-400 mb-1">Tutor</p>
                        <p class="font-semibold text-slate-700">{{ $selectedMeeting->tutor?->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-black text-slate-700 mb-3">Attendance Records</h4>
                    <div class="space-y-2">
                        @foreach ($selectedMeeting->attendances as $attendance)
                            <div
                                class="p-3 rounded-xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-slate-700">{{ $attendance->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $attendance->role }} | Joined:
                                        {{ optional($attendance->joined_at)->format('M d, h:i A') ?? 'N/A' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button wire:click="markAttendance({{ $attendance->id }}, 'scheduled')"
                                        class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">Scheduled</button>
                                    <button wire:click="markAttendance({{ $attendance->id }}, 'attended')"
                                        class="text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-700">Attended</button>
                                    <button wire:click="markAttendance({{ $attendance->id }}, 'missed')"
                                        class="text-xs px-2 py-1 rounded bg-rose-100 text-rose-700">Missed</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
