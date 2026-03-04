<div class="p-3 sm:p-4 lg:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-black text-xl sm:text-2xl text-slate-800">Bootcamp <span class="text-cyan-600">Manager</span>
            </h2>
            <button x-on:click="$dispatch('open-create-cohort')"
                class="w-full sm:w-auto bg-cyan-600 hover:bg-cyan-700 text-white px-5 py-2 rounded-xl font-bold">
                New Cohort
            </button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-3 sm:p-4 rounded-2xl border border-slate-100 mb-6 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" placeholder="Search name, code, track..."
            class="flex-1 min-w-full sm:min-w-[220px] rounded-xl border-slate-200 text-sm" />
        <select wire:model.live="serviceFilter" class="w-full sm:w-auto rounded-xl border-slate-200 text-sm">
            <option value="all">All Bootcamp Services</option>
            @foreach ($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="w-full sm:w-auto rounded-xl border-slate-200 text-sm">
            <option value="all">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="open">Open</option>
            <option value="running">Running</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select wire:model.live="serviceItemFilter" class="w-full sm:w-auto rounded-xl border-slate-200 text-sm">
            <option value="all">All Tracks</option>
            @foreach ($serviceItemsForFilter as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-cyan-700">
                    <tr>
                        <th class="px-3 sm:px-5 py-3 text-xs text-white uppercase">Cohort</th>
                        <th class="px-3 sm:px-5 py-3 text-xs text-white uppercase">Track</th>
                        <th class="px-3 sm:px-5 py-3 text-xs text-white uppercase">Dates</th>
                        <th class="px-3 sm:px-5 py-3 text-xs text-white uppercase">Status</th>
                        <th class="px-3 sm:px-5 py-3 text-xs text-white uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($cohorts as $cohort)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 sm:px-5 py-4">
                                <div class="font-bold text-slate-800">{{ $cohort->name }}</div>
                                <div class="text-xs text-cyan-600">{{ $cohort->code }}</div>
                                <div class="text-xs text-slate-400">{{ $cohort->enrollees_count }} enrollees</div>
                            </td>
                            <td class="px-3 sm:px-5 py-4 text-sm text-slate-700">
                                {{ $cohort->serviceItem->name ?? 'N/A' }}</td>
                            <td class="px-3 sm:px-5 py-4 text-sm text-slate-600">
                                {{ \Carbon\Carbon::parse($cohort->start_date)->format('M d, Y') }} -
                                {{ $cohort->end_date ? \Carbon\Carbon::parse($cohort->end_date)->format('M d, Y') : 'Open' }}
                            </td>
                            <td class="px-3 sm:px-5 py-4 text-sm uppercase font-semibold">{{ $cohort->status }}</td>
                            <td class="px-3 sm:px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="viewCohort({{ $cohort->id }})"
                                        class="text-cyan-600 text-sm">View</button>
                                    <button wire:click="editCohort({{ $cohort->id }})"
                                        class="text-amber-600 text-sm">Edit</button>
                                    <button wire:click="openDeleteCohort({{ $cohort->id }})"
                                        class="text-rose-600 text-sm">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 sm:px-5 py-10 text-center text-slate-400">No cohorts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 sm:px-5 py-3 border-t border-slate-100 bg-slate-50">
            {{ $cohorts->links() }}
        </div>
    </div>

    @if ($showCohortForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60" wire:click="$set('showCohortForm', false)"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto p-4 sm:p-6">
                <h3 class="font-black text-lg mb-4">{{ $editingCohortId ? 'Edit' : 'Create' }} Cohort</h3>
                <form wire:submit.prevent="saveCohort" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500">Service</label>
                        <select wire:model.live="service_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">Select Service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Track</label>
                        <select wire:model="service_item_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">Select Track</option>
                            @foreach ($serviceItems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('service_item_id')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Name</label>
                        <input wire:model="name" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('name')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Code</label>
                        <input wire:model="code" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('code')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Status</label>
                        <select wire:model="cohort_status" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="draft">Draft</option>
                            <option value="open">Open</option>
                            <option value="running">Running</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Start Date</label>
                        <input type="date" wire:model="start_date"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('start_date')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">End Date</label>
                        <input type="date" wire:model="end_date"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('end_date')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Mode</label>
                        <select wire:model="mode" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="online">Online</option>
                            <option value="physical">Physical</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Location</label>
                        <input wire:model="location" class="w-full rounded-xl border-slate-200 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Capacity</label>
                        <input type="number" wire:model="capacity"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Fee</label>
                        <input type="number" step="0.01" wire:model="fee"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-500">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-xl border-slate-200 text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2 flex flex-col sm:flex-row sm:justify-end gap-2">
                        <button type="button" wire:click="$set('showCohortForm', false)"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Cancel</button>
                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-cyan-600 text-white font-semibold">{{ $editingCohortId ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showCohortDetail && $selectedCohort)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60" wire:click="closeDetail"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-4xl max-h-[92vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4">
                    <div>
                        <h3 class="font-black text-lg">{{ $selectedCohort->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $selectedCohort->code }} •
                            {{ $selectedCohort->serviceItem->name ?? 'Track' }}</p>
                    </div>
                    <button wire:click="openCreateEnrollee({{ $selectedCohort->id }})"
                        class="w-full sm:w-auto bg-cyan-600 text-white px-3 py-2 rounded-xl text-sm font-semibold">Add
                        Enrollee</button>
                </div>
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs uppercase text-slate-500">Name</th>
                                <th class="px-4 py-3 text-xs uppercase text-slate-500">Contact</th>
                                <th class="px-4 py-3 text-xs uppercase text-slate-500">Status</th>
                                <th class="px-4 py-3 text-xs uppercase text-slate-500 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($selectedCohort->enrollees as $enrollee)
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-slate-700">{{ $enrollee->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $enrollee->address }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        <div>{{ $enrollee->email }}</div>
                                        <div class="text-xs">{{ $enrollee->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm uppercase">{{ $enrollee->status }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            @if (!$enrollee->is_read)
                                                <button wire:click="markAsRead({{ $enrollee->id }})"
                                                    class="text-cyan-600 text-sm">Seen</button>
                                            @endif
                                            <button wire:click="editEnrollee({{ $enrollee->id }})"
                                                class="text-amber-600 text-sm">Edit</button>
                                            <button wire:click="deleteEnrollee({{ $enrollee->id }})"
                                                class="text-rose-600 text-sm">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400">No enrollees yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($showEnrolleeForm)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60" wire:click="$set('showEnrolleeForm', false)"></div>
            <div class="relative bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto p-4 sm:p-6">
                <h3 class="font-black text-lg mb-4">{{ $editingEnrolleeId ? 'Edit' : 'Add' }} Enrollee</h3>
                <form wire:submit.prevent="saveEnrollee" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500">Cohort</label>
                        <select wire:model="cohort_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">Select Cohort</option>
                            @foreach ($allCohorts as $cohortOption)
                                <option value="{{ $cohortOption->id }}">{{ $cohortOption->name }}
                                    ({{ $cohortOption->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('cohort_id')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Status</label>
                        <select wire:model="enrollee_status" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Name</label>
                        <input wire:model="enrollee_name" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('enrollee_name')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Email</label>
                        <input type="email" wire:model="enrollee_email"
                            class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('enrollee_email')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Phone</label>
                        <input wire:model="enrollee_phone" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('enrollee_phone')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Address</label>
                        <input wire:model="enrollee_address" class="w-full rounded-xl border-slate-200 text-sm" />
                        @error('enrollee_address')
                            <span class="text-rose-600 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-500">Notes</label>
                        <textarea wire:model="enrollee_notes" rows="2" class="w-full rounded-xl border-slate-200 text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input id="is_read" type="checkbox" wire:model="is_read"
                            class="rounded border-slate-300" />
                        <label for="is_read" class="text-sm text-slate-600">Mark as reviewed</label>
                    </div>
                    <div class="md:col-span-2 flex flex-col sm:flex-row sm:justify-end gap-2">
                        <button type="button" wire:click="$set('showEnrolleeForm', false)"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Cancel</button>
                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-cyan-600 text-white font-semibold">{{ $editingEnrolleeId ? 'Update' : 'Add' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDelete)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60"></div>
            <div class="relative bg-white rounded-2xl p-5 sm:p-6 max-w-sm w-full">
                <h3 class="font-black text-lg mb-2">Delete Cohort?</h3>
                <p class="text-sm text-slate-500 mb-5">This removes the cohort and all linked enrollees.</p>
                <div class="flex flex-col sm:flex-row sm:justify-end gap-2">
                    <button wire:click="$set('showDelete', false)"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Cancel</button>
                    <button wire:click="deleteCohort"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-600 text-white font-semibold">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
