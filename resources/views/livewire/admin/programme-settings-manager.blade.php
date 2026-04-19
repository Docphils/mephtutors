<div class="p-3 sm:p-4 lg:p-6 bg-cyan-100 min-h-screen space-y-6">
    <x-slot name="header">
        <h2 class="font-black text-xl sm:text-2xl text-slate-800">Intervention <span class="text-cyan-600">Settings</span></h2>
    </x-slot>

    @if (session('success'))
        <div class="px-4 py-3 rounded-xl bg-emerald-500 text-white text-sm font-bold">{{ session('success') }}</div>
    @endif

    <section class="bg-white rounded-2xl border border-slate-100 p-5 space-y-4 shadow-sm">
        <h3 class="text-lg font-black text-slate-800">Homepage Mode &amp; Promise Settings</h3>
        <form wire:submit.prevent="saveSiteSettings" class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Homepage Mode</label>
                <select wire:model="homepage_mode" class="w-full rounded-xl border-slate-200">
                    <option value="default">Default Existing Homepage</option>
                    <option value="academic">Academic Intervention Homepage</option>
                </select>
                @error('homepage_mode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">WhatsApp Number (digits only)</label>
                <input wire:model="programme_whatsapp_number" type="text" class="w-full rounded-xl border-slate-200">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Default Pricing Note</label>
                <textarea wire:model="programme_pricing_note_default" rows="2" class="w-full rounded-xl border-slate-200"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input type="checkbox" wire:model="programme_trial_class_enabled" class="rounded border-slate-300 text-cyan-600">
                Trial Class Enabled
            </label>

            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input type="checkbox" wire:model="programme_probationary_classes_allowed" class="rounded border-slate-300 text-cyan-600">
                Up to Two Probationary Classes Allowed
            </label>

            <div class="md:col-span-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                    Save Settings
                </button>
            </div>
        </form>
    </section>

    <section class="bg-white rounded-2xl border border-slate-100 p-5 space-y-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <h3 class="text-lg font-black text-slate-800">Manage Intervention Pages</h3>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search interventions..."
                class="w-full md:w-72 rounded-xl border-slate-200 text-sm">
        </div>

        <form wire:submit.prevent="saveProgramme" class="grid md:grid-cols-2 gap-4 p-4 rounded-2xl bg-cyan-700/95">
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Intervention Name</label>
                <input wire:model="name" type="text" class="w-full rounded-xl border-slate-200">
                @error('name') <p class="text-xs text-rose-100 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Slug (optional)</label>
                <input wire:model="slug" type="text" class="w-full rounded-xl border-slate-200">
                @error('slug') <p class="text-xs text-rose-100 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Tagline</label>
                <textarea wire:model="tagline" rows="2" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Summary</label>
                <textarea wire:model="summary" rows="2" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Overview</label>
                <textarea wire:model="overview" rows="3" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Who It Is For</label>
                <textarea wire:model="who_it_is_for" rows="3" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">What Parents Can Expect</label>
                <textarea wire:model="what_parents_can_expect" rows="3" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Frequency Options (comma-separated)</label>
                <input wire:model="frequency_options" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Duration Options (comma-separated)</label>
                <input wire:model="duration_options" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Mode Options (comma-separated)</label>
                <input wire:model="mode_options" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Starting From Text</label>
                <input wire:model="starting_from_text" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Renewability Note</label>
                <input wire:model="renewability_note" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Linked Service Item</label>
                <select wire:model="service_item_id" class="w-full rounded-xl border-slate-200">
                    <option value="">Auto-select default</option>
                    @foreach ($serviceItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Sort Order</label>
                <input wire:model="sort_order" type="number" min="0" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Max Selectable Subjects</label>
                <input wire:model="max_selectable_subjects" type="number" min="1" max="20" class="w-full rounded-xl border-slate-200">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Pricing Note</label>
                <textarea wire:model="pricing_note" rows="2" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Subject Options (comma-separated)</label>
                <textarea wire:model="subject_options" rows="3" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">Pricing Matrix JSON</label>
                <textarea wire:model="pricing_matrix" rows="7" class="w-full rounded-xl border-slate-200 font-mono text-xs"
                    placeholder='{"frequency_prices":{"2x weekly":45000},"duration_multipliers":{"1 hour":1.0},"mode_multipliers":{"online":1.0,"home":1.2},"additional_subject_fraction":0.35,"base_subject_allowance":1,"location_surcharge":5000}'></textarea>
                @error('pricing_matrix') <p class="text-xs text-rose-100 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-cyan-50 mb-1">FAQs (one per line, format: Question | Answer)</label>
                <textarea wire:model="faq_items" rows="4" class="w-full rounded-xl border-slate-200"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Meta Title</label>
                <input wire:model="meta_title" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Meta Description</label>
                <input wire:model="meta_description" type="text" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">OG Image Path (optional)</label>
                <input wire:model="og_image" type="text" class="w-full rounded-xl border-slate-200" placeholder="images/banner.jpg">
            </div>
            <div>
                <label class="block text-xs font-bold text-cyan-50 mb-1">Hero Image Path</label>
                <input wire:model="hero_image" type="text" class="w-full rounded-xl border-slate-200" placeholder="images/banner2.jpg">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-bold text-cyan-50 flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-cyan-600">
                    Active
                </label>
            </div>
            <div class="md:col-span-2 flex flex-wrap gap-2">
                @if ($editingId)
                    <button type="button" wire:click="resetProgrammeForm"
                        class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">Cancel Edit</button>
                @endif
                <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 text-white font-black hover:bg-cyan-400 transition">
                    {{ $editingId ? 'Update Intervention' : 'Create Intervention' }}
                </button>
            </div>
        </form>

        <div class="overflow-x-auto border border-slate-100 rounded-2xl">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Intervention</th>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Slug</th>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($programmes as $programme)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $programme->name }}</div>
                                <div class="text-xs text-slate-500">{{ $programme->starting_from_text }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $programme->slug }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $programme->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $programme->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button wire:click="editProgramme({{ $programme->id }})" class="text-cyan-600 font-bold text-xs uppercase">Edit</button>
                                <button wire:click="deleteProgramme({{ $programme->id }})" wire:confirm="Delete this intervention?"
                                    class="text-rose-500 font-bold text-xs uppercase">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No interventions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $programmes->links() }}
    </section>
</div>
