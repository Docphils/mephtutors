{{-- resources/views/livewire/admin/tutor-profile-view.blade.php --}}

<div class="min-h-screen bg-[#F8FAFC] p-4 lg:p-8">

    <div class="max-w-6xl mx-auto">

        {{-- BACK BUTTON --}}
        <div class="mb-6">
            <a wire:navigate href="{{ route('admin.tutorProfile') }}"
                class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-600 font-bold hover:bg-slate-100 transition">
                <i class="fa fa-arrow-left"></i>
                Back to Tutor List
            </a>
        </div>

        {{-- =========================
            TOP SUMMARY HEADER
        ========================== --}}
        <div
            class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 mb-8 flex flex-col md:flex-row items-center gap-6">

            <div class="relative">
                <div class="w-24 h-24 rounded-2xl bg-cyan-100 overflow-hidden border-4 border-white shadow-sm">
                    @if ($profile && $profile->image)
                        <img src="{{ asset('storage/' . $profile->image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-cyan-600 font-bold text-2xl">
                            {{ substr($profile->fullName ?? 'T', 0, 1) }}
                        </div>
                    @endif
                </div>

                <div
                    class="absolute -bottom-2 -right-2  text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-sm uppercase tracking-tighter {{ $profile->status === 'Approved' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                    {{ $profile->status }}
                </div>
            </div>

            <div class="flex-1 text-center md:text-left">
                <h1 class="text-2xl font-black text-slate-800">
                    {{ $profile->fullName }}
                </h1>

                <p class="text-slate-500 font-medium">
                    {{ $profile->discipline ?? 'Tutor' }}
                    •
                    {{ $profile->qualification ?? 'N/A' }}
                </p>

                <div class="mt-2 flex flex-wrap justify-center md:justify-start gap-4">
                    <span class="text-xs font-bold text-slate-400">
                        <i class="fa-solid fa-envelope mr-1 text-cyan-500"></i>
                        {{ $profile->user->email ?? 'N/A' }}
                    </span>

                    <span class="text-xs font-bold text-slate-400">
                        <i class="fa-solid fa-phone mr-1 text-cyan-500"></i>
                        {{ $profile->phone ?? 'Not Set' }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <button wire:click="editTutorProfile({{ $profile->id }})"
                    class="px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-bold text-sm hover:bg-cyan-700 transition-all">
                    Edit Approval
                </button>
            </div>
        </div>
        {{-- =========================
                MAIN CONTENT
            ========================== --}}
        <div class=" space-y-6">

            {{-- SUCCESS --}}
            @if (session()->has('success'))
                <div class="p-4 bg-emerald-500 text-white rounded-2xl font-bold flex items-center gap-3">
                    <i class="fa-solid fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- PROFILE CARD --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="bg-slate-800 p-6">
                    <h3 class="text-white font-black text-lg">
                        Full Profile Record
                    </h3>
                    <p class="text-slate-400 text-xs">
                        Complete overview of tutor registry data.
                    </p>
                </div>

                <div class="p-8 space-y-8">

                    {{-- PERSONAL --}}
                    <section>
                        <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">
                            Personal Details
                        </h4>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl">

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Full Name
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->fullName }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Gender
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->gender ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Date of Birth
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->DOB ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Address
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->address ?? 'N/A' }}
                                </p>
                            </div>

                        </div>
                    </section>

                    {{-- ACADEMIC --}}
                    <section>
                        <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">
                            Academic & Professional
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl">

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Qualification
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->qualification ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Specialization
                                </label>
                                <p class="text-slate-800 font-bold">
                                    {{ $profile->discipline ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Career Profile
                                </label>
                                <p class="text-slate-700 text-sm leading-relaxed">
                                    {{ $profile->careerProfile ?? 'No bio provided.' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-[9px] font-black uppercase">
                                    Experience
                                </label>
                                <span
                                    class="inline-block bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full text-xs font-black">
                                    {{ $profile->experience ?? 'N/A' }}
                                </span>
                            </div>

                        </div>
                    </section>

                    {{-- BANKING --}}
                    <section>
                        <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">
                            Payment & Banking
                        </h4>

                        <div class="bg-slate-900 text-white p-6 rounded-2xl flex flex-wrap gap-8">

                            <div>
                                <label class="block text-slate-500 text-[9px] font-black uppercase">
                                    Bank
                                </label>
                                <p class="font-bold tracking-tight">
                                    {{ $profile->bankName ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-500 text-[9px] font-black uppercase">
                                    Account Name
                                </label>
                                <p class="font-bold tracking-tight">
                                    {{ $profile->accountName ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-slate-500 text-[9px] font-black uppercase">
                                    Account Number
                                </label>
                                <p class="font-bold tracking-tight text-emerald-400">
                                    {{ $profile->accountNumber ?? 'N/A' }}
                                </p>
                            </div>

                        </div>
                    </section>

                    {{-- MEDIA --}}
                    <div class="grid space-y-4">

                        <div>
                            <h4 class="text-xs font-black text-cyan-600 uppercase tracking-widest mb-1">
                                Media Assets
                            </h4>
                            <p class="text-xs text-slate-400 font-medium">
                                Introduction video and documents.
                            </p>
                        </div>

                        {{-- CV --}}
                        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm">

                            <div class="flex items-center gap-4 mb-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                                    <i class="fa-solid fa-file-pdf text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">
                                        Curriculum Vitae (CV)
                                    </p>
                                    <p class="text-[10px] text-slate-400">
                                        PDF Document
                                    </p>
                                </div>
                            </div>

                            @if ($profile->CV)
                                <iframe src="{{ asset('storage/' . $profile->CV) }}"
                                    class="w-full h-[400px] rounded-xl border border-slate-200"></iframe>
                            @else
                                <span class="text-[10px] font-bold text-rose-400">
                                    NOT UPLOADED
                                </span>
                            @endif
                        </div>

                        {{-- VIDEO --}}
                        @if ($profile->video)
                            <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-xl border-4 border-slate-800">
                                <video controls class="w-full aspect-video">
                                    <source src="{{ asset('storage/' . $profile->video) }}">
                                </video>

                                <div class="p-3 bg-slate-800 text-center">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Introduction Video Preview
                                    </p>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Modal for Editing Status and Approval Remark -->
    @if ($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md text-black">
                <h3 class="text-lg font-semibold mb-4">Edit Tutor Profile</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status"
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Status</option>
                        <option value="Approved">Approved</option>
                        <option value="Review">Review</option>
                    </select>
                    @error('status')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Approval Remark</label>
                    <textarea wire:model="approvalRemark"
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        rows="3"></textarea>
                    @error('approvalRemark')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                    <button wire:click="saveTutorProfile"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
