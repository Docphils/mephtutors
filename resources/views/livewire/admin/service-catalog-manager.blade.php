<div class="p-6 bg-cyan-100 min-h-screen space-y-6">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800">Service <span class="text-cyan-600">Catalog</span></h2>
                <p class="text-sm text-slate-500 font-medium">Manage services, items, exams, and levels</p>
            </div>
            <a wire:navigate href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-slate-200 text-cyan-700 text-sm font-bold shadow-sm hover:bg-slate-50 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div
            class="px-4 py-3 rounded-xl bg-emerald-500 text-white text-sm font-bold shadow-lg shadow-emerald-200/50 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-100 hover:text-white">&times;</button>
        </div>
    @endif

    <div class="flex flex-wrap gap-2 p-1 bg-cyan-700 rounded-2xl w-fit">
        @foreach (['services' => 'Services', 'items' => 'Service Items', 'exams' => 'Exam Types', 'levels' => 'Levels'] as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all {{ $currentTab === $key ? 'bg-white text-cyan-600 shadow-sm' : 'text-slate-100 hover:bg-white/50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="w-full">
        {{-- SERVICES TAB --}}
        @if ($currentTab === 'services')
            <section
                class="bg-white rounded-2xl border border-slate-100 p-6 space-y-6 shadow-sm animate-in fade-in duration-300">
                <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                    <h3 class="text-lg font-black text-slate-800">Manage Services</h3>
                    <span class="px-3 py-1 bg-cyan-50 text-cyan-700 text-xs font-bold rounded-full">Total:
                        {{ $services->total() }}</span>
                </div>

                <form wire:submit.prevent="saveService"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-cyan-700 rounded-2xl border border-slate-100">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Service Name</label>
                        <input wire:model="service_name" placeholder="e.g. Home Tutoring"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Slug (Optional)</label>
                        <input wire:model="service_slug" placeholder="home-tutoring"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Target Group</label>
                        <select wire:model="service_target"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                            <option value="tutor_request">Tutor Request</option>
                            <option value="institutions">Institutions</option>
                            <option value="bootcamp">Bootcamp</option>
                        </select>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm font-bold text-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model="service_is_active"
                                class="w-5 h-5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                            Active
                        </label>
                    </div>
                    <div class="md:col-span-2 lg:col-span-3">
                        <textarea wire:model="service_description" placeholder="Brief description of the service..." rows="1"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"></textarea>
                    </div>
                    <div class="flex gap-2 items-center">
                        @if ($serviceEditingId)
                            <button type="button" wire:click="resetServiceForm"
                                class="flex-1 px-4 py-2 text-sm rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">Cancel</button>
                        @endif
                        <button type="submit"
                            class="flex-[2] px-4 py-2 text-sm rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition shadow-md shadow-cyan-200">
                            {{ $serviceEditingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>

                <div class="space-y-4">
                    <div class="relative group">
                        <input wire:model.live.debounce.300ms="serviceSearch" placeholder="Search services..."
                            class="w-full pl-10 rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500" />
                        <svg class="w-4 h-4 absolute left-4 top-3 text-slate-400 group-focus-within:text-cyan-500 transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-cyan-700 border-b border-slate-100">
                                <tr class="text-xs font-black uppercase text-slate-100">
                                    <th class="px-4 py-3">Service Name</th>
                                    <th class="px-4 py-3">Slug</th>
                                    <th class="px-4 py-3">Items</th>
                                    <th class="px-4 py-3 text-right">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($services as $service)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-bold text-slate-700">{{ $service->name }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ $service->slug }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-0.5 bg-slate-100 rounded-lg text-slate-600 font-bold">{{ $service->service_items_count }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-3">
                                                <button wire:click="editService({{ $service->id }})"
                                                    class="text-cyan-600 hover:text-cyan-800 font-bold text-xs uppercase tracking-wider">Edit</button>
                                                <button wire:click="deleteService({{ $service->id }})"
                                                    wire:confirm="Are you sure?"
                                                    class="text-rose-500 hover:text-rose-700 font-bold text-xs uppercase tracking-wider">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-4 py-12 text-center text-sm text-slate-400 font-medium">No
                                            services found matching your criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $services->links() }}
                </div>
            </section>
        @endif

        {{-- SERVICE ITEMS TAB --}}
        @if ($currentTab === 'items')
            <section
                class="bg-white rounded-2xl border border-slate-100 p-6 space-y-6 shadow-sm animate-in fade-in duration-300">
                <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                    <h3 class="text-lg font-black text-slate-800">Manage Service Items</h3>
                    <span class="px-3 py-1 bg-cyan-50 text-cyan-700 text-xs font-bold rounded-full">Total:
                        {{ $serviceItems->total() }}</span>
                </div>

                <form wire:submit.prevent="saveServiceItem"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5 bg-cyan-700 rounded-2xl border border-slate-100">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Parent Service</label>
                        <select wire:model="item_service_id"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                            <option value="">Select parent service</option>
                            @foreach ($serviceOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Item Name</label>
                        <input wire:model="item_name" placeholder="Item name"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-100 ml-1">Position & Target</label>
                        <div class="flex gap-2">
                            <input wire:model.live="item_display_position" type="number" placeholder="Pos"
                                class="w-20 rounded-xl border-slate-200 text-sm" />
                            <select wire:model="item_target" class="flex-1 rounded-xl border-slate-200 text-sm">
                                <option value="tutor_request">Tutor Request</option>
                                <option value="institutions">Institutions</option>
                                <option value="bootcamp">Bootcamp</option>
                            </select>
                        </div>
                    </div>

                    <div
                        class="lg:col-span-3 grid grid-cols-2 md:grid-cols-5 gap-3 bg-white p-3 rounded-xl border border-slate-200">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox"
                                wire:model="item_shown_on_welcome" class="rounded text-cyan-600"> Welcome</label>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox"
                                wire:model="item_has_subjects" class="rounded text-cyan-600"> Subjects</label>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox"
                                wire:model="item_requires_curriculum" class="rounded text-cyan-600">
                            Curriculum</label>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox"
                                wire:model="item_requires_level" class="rounded text-cyan-600"> Level</label>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox"
                                wire:model="item_requires_exam_type" class="rounded text-cyan-600"> Exam Type</label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-100 mb-1 ml-1">Hero Image</label>
                        <input type="file" wire:model="item_image"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100" />
                        <div wire:loading wire:target="item_image"
                            class="text-[10px] text-cyan-600 font-bold mt-1 uppercase">Uploading...</div>
                    </div>

                    <div class="flex gap-2 items-end">
                        @if ($itemEditingId)
                            <button type="button" wire:click="resetServiceItemForm"
                                class="flex-1 px-4 py-2 text-sm rounded-xl bg-slate-200 text-slate-700 font-bold">Cancel</button>
                        @endif
                        <button type="submit"
                            class="flex-[2] px-4 py-2 text-sm rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition">
                            {{ $itemEditingId ? 'Update Item' : 'Create Item' }}
                        </button>
                    </div>
                </form>

                <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Item</th>
                                <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Parent Service</th>
                                <th class="px-4 py-3 text-xs font-black uppercase text-slate-500 text-right">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($serviceItems as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-bold text-slate-700">{{ $item->name }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-tighter">
                                            {{ $item->slug }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item->service->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-3">
                                            <button wire:click="editServiceItem({{ $item->id }})"
                                                class="text-cyan-600 font-bold text-xs uppercase">Edit</button>
                                            <button wire:click="deleteServiceItem({{ $item->id }})"
                                                class="text-rose-500 font-bold text-xs uppercase">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-slate-400">No items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $serviceItems->links() }}
            </section>
        @endif

        {{-- EXAMS & LEVELS TAB (Combined or separate) --}}
        @if ($currentTab === 'exams' || $currentTab === 'levels')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in duration-300">
                @if ($currentTab === 'exams')
                    <section class="bg-white rounded-2xl border border-slate-100 p-6 space-y-4 shadow-sm">
                        <h3 class="text-lg font-black text-slate-800 border-b border-slate-50 pb-3">Exam Types</h3>
                        <form wire:submit.prevent="saveExamType" class="flex gap-2">
                            <input wire:model="exam_name" placeholder="Exam Name"
                                class="flex-1 rounded-xl border-slate-200 text-sm" />
                            <button type="submit"
                                class="px-4 py-2 bg-cyan-600 text-white rounded-xl font-bold text-sm">{{ $examEditingId ? 'Update' : 'Add' }}</button>
                        </form>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-cyan-700 font-bold text-slate-100">
                                    <tr>
                                        <th class="px-4 py-2">Name</th>
                                        <th class="px-4 py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($examTypes as $exam)
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ $exam->name }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <button wire:click="editExamType({{ $exam->id }})"
                                                    class="text-cyan-600 mr-2">Edit</button>
                                                <button wire:click="deleteExamType({{ $exam->id }})"
                                                    class="text-rose-500">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($currentTab === 'levels')
                    <section class="bg-white rounded-2xl border border-slate-100 p-6 space-y-4 shadow-sm">
                        <h3 class="text-lg font-black text-slate-800 border-b border-slate-50 pb-3">Academic Levels
                        </h3>
                        <form wire:submit.prevent="saveLevel" class="flex gap-2">
                            <input wire:model="level_name" placeholder="Level Name"
                                class="flex-1 rounded-xl border-slate-200 text-sm" />
                            <input wire:model="level_order" type="number" placeholder="Order"
                                class="w-20 rounded-xl border-slate-200 text-sm" />
                            <button type="submit"
                                class="px-4 py-2 bg-cyan-600 text-white rounded-xl font-bold text-sm">{{ $levelEditingId ? 'Update' : 'Add' }}</button>
                        </form>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-cyan-700 font-bold text-slate-100">
                                    <tr>
                                        <th class="px-4 py-2">Name</th>
                                        <th class="px-4 py-2">Order</th>
                                        <th class="px-4 py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($levels as $level)
                                        <tr>
                                            <td class="px-4 py-2 font-medium">{{ $level->name }}</td>
                                            <td class="px-4 py-2">{{ $level->order }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <button wire:click="editLevel({{ $level->id }})"
                                                    class="text-cyan-600 mr-2">Edit</button>
                                                <button wire:click="deleteLevel({{ $level->id }})"
                                                    class="text-rose-500">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </div>
</div>
