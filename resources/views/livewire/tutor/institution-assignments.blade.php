<div class="p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <i class="fa-solid fa-building-circle-check text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Institution <span class="text-cyan-600">Assignments</span></h2>
                <p class="text-slate-500 text-sm font-medium">Track your assigned school and institution deployments.</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search institution, service or location..."
            class="flex-1 bg-slate-50 border-transparent rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-cyan-500">
        <select wire:model.live="status" class="bg-slate-50 border-transparent rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-cyan-500">
            <option value="all">All Statuses</option>
            <option value="assigned">Assigned</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="released">Released</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse ($assignments as $assignment)
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">{{ $assignment->crm?->institution_name }}</h3>
                        <p class="text-xs text-cyan-600 font-bold">{{ $assignment->crm?->serviceItem?->name }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase bg-cyan-100 text-cyan-700">
                        {{ $assignment->status }}
                    </span>
                </div>
                <p class="mt-3 text-sm text-slate-600">{{ $assignment->crm?->institution_address }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-[10px] uppercase text-slate-500 font-bold">Role: {{ $assignment->role }}</p>
                    <button wire:click="viewAssignment({{ $assignment->id }})"
                        class="px-4 py-2 rounded-xl bg-cyan-600 text-white text-xs font-bold hover:bg-cyan-700">
                        View Details
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-dashed border-slate-200 py-16 text-center text-slate-500">
                No institution assignments yet.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $assignments->links() }}
    </div>

    @if ($showModal && $selectedAssignment)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl max-w-2xl w-full overflow-hidden">
                <div class="p-6 bg-cyan-700 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black">{{ $selectedAssignment->crm?->institution_name }}</h3>
                        <p class="text-xs text-cyan-100 uppercase font-bold">{{ $selectedAssignment->crm?->serviceItem?->service?->name }} | {{ $selectedAssignment->role }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-white/80 hover:text-white"><i class="fa fa-times text-lg"></i></button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-2xl p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Status</p>
                            <p class="text-sm font-bold text-slate-800">{{ $selectedAssignment->status }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Tutors Required</p>
                            <p class="text-sm font-bold text-slate-800">{{ $selectedAssignment->crm?->number_of_tutors_required }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Client Contact</p>
                        <p class="text-sm font-bold text-slate-800">{{ $selectedAssignment->crm?->user?->name }} ({{ $selectedAssignment->crm?->user?->email }})</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Contract Scope</p>
                        <div class="bg-slate-50 rounded-2xl p-4 text-sm text-slate-700 whitespace-pre-line">{!! $selectedAssignment->crm?->contract_terms ?: 'No contract terms shared yet.' !!}</div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Requirements</p>
                        <div class="bg-slate-50 rounded-2xl p-4 text-sm text-slate-700">{{ $selectedAssignment->crm?->requirements ?: 'No additional requirements.' }}</div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    <button wire:click="closeModal" class="w-full py-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-black">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
