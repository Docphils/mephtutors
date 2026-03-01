<div class="max-w-7xl mx-auto p-4 sm:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="font-black text-lg sm:text-2xl text-slate-800 tracking-tight leading-tight">
                        Tutor Profile <span class="text-cyan-600">Manager</span>
                    </h2>
                    <p class="hidden sm:block text-slate-500 text-xs sm:text-sm font-medium mt-1">
                        Review and manage the profile of tutors.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-xl border border-green-200 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="relative w-full lg:w-1/3">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" wire:model.live="search" placeholder="Search by name or address..."
                class="block w-full pl-10 pr-4 py-2.5 border-none rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-cyan-500 text-slate-700 placeholder-slate-400">
        </div>

        <div class="grid grid-cols-2 sm:flex sm:justify-end gap-3 w-full lg:w-2/3">
            <select wire:change="setTab($event.target.value)"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-cyan-500 hover:bg-cyan-600 border-none shadow-sm cursor-pointer transition-colors">
                <option value="All" {{ $activeTab == 'All' ? 'selected' : '' }}>All Qualifications</option>
                <option value="SSCE" {{ $activeTab == 'SSCE' ? 'selected' : '' }}>SSCE</option>
                <option value="Diploma" {{ $activeTab == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                <option value="NCE" {{ $activeTab == 'NCE' ? 'selected' : '' }}>NCE</option>
                <option value="HND/BSc/BEd/BA/BEng" {{ $activeTab == 'HND/BSc/BEd/BA/BEng' ? 'selected' : '' }}>Degree
                </option>
                <option value="MSc/MA" {{ $activeTab == 'MSc/MA' ? 'selected' : '' }}>MSc/MA</option>
                <option value="PhD" {{ $activeTab == 'PhD' ? 'selected' : '' }}>PhD</option>
            </select>

            <select wire:change="setTab($event.target.value)"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 border-none shadow-sm cursor-pointer transition-colors">
                <option value="Science" {{ $activeTab == 'Science' ? 'selected' : '' }}>Science</option>
                <option value="Arts" {{ $activeTab == 'Arts' ? 'selected' : '' }}>Arts</option>
                <option value="Commerce" {{ $activeTab == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                <option value="Approved" {{ $activeTab == 'Approved' ? 'selected' : '' }}>Approved Only</option>
                <option value="Pending" {{ $activeTab == 'Pending' ? 'selected' : '' }}>Pending Only</option>
            </select>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cyan-700 text-white text-[8px] sm:text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-3 ">Full Name</th>
                        <th class="px-3 py-3 ">Qualification</th>
                        <th class="px-3 py-3  hidden md:table-cell">State</th>
                        <th class="px-3 py-3 ">Status</th>
                        <th class="px-3 py-3  text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tutorProfiles as $profile)
                        <tr class="hover:bg-slate-50 transition-colors text-xs sm:text-sm">
                            <td class="px-3 py-3">
                                <div class=" font-bold text-slate-800">{{ $profile->fullName }}</div>
                                <div class="text-[10px] text-slate-400 md:hidden">{{ $profile->state }}</div>
                            </td>
                            <td class="px-3 py-3  text-slate-600">
                                {{ $profile->qualification === 'HND/BSc/BEd/BA/BEng' ? 'Degree' : $profile->qualification }}
                            </td>
                            <td class="px-3 py-3  text-slate-600 hidden md:table-cell">
                                {{ $profile->state }}
                            </td>
                            <td class="px-3 py-3">
                                <span
                                    class="px-3 py-1 text-[10px] leading-5 font-bold rounded-full {{ $profile->status == 'Approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ strtoupper($profile->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <a wire:navigate href="{{ route('admin.tutors.view', $profile->id) }}"
                                    class="inline-flex items-center px-4 py-1.5 bg-cyan-50 text-cyan-600 font-bold text-xs rounded-lg border border-cyan-100 hover:bg-cyan-600 hover:text-white transition-all">
                                    VIEW
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">
                                No tutor profiles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tutorProfiles->hasPages())
            <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                {{ $tutorProfiles->links() }}
            </div>
        @endif
    </div>
</div>
