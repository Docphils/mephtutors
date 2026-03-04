<div class="min-h-screen bg-[#F8FAFC] p-4 lg:p-8">
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <i class="fa-solid fa-user-gear text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Manage <span
                        class="text-cyan-600">Profile</span></h2>
                <p class="text-slate-500 text-sm font-medium">View and manage your personal, professional, financial and
                    academic profile.
                </p>
            </div>
        </div>
    </x-slot>
    <div class="max-w-6xl mx-auto">

        {{-- Top Summary Header --}}
        <div
            class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 mb-8 flex flex-col md:flex-row items-center gap-6">
            <div class="relative">
                <div class="w-24 h-24 rounded-2xl bg-cyan-100 overflow-hidden border-4 border-white shadow-sm">
                    @if ($profile && $profile->image)
                        <img src="{{ asset('storage/' . $profile->image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-cyan-600 font-bold text-2xl">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div
                    class="absolute -bottom-2 -right-2 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-sm uppercase tracking-tighter {{ $profile?->status === 'Approved' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                    {{ $profile?->status }}
                </div>
            </div>

            <div class="flex-1 text-center md:text-left">
                <h1 class="text-2xl font-black text-slate-800">{{ Auth::user()->name }}</h1>
                <p class="text-slate-500 font-medium">{{ $profile->discipline ?? 'Educational Consultant' }} •
                    {{ $profile->qualification ?? 'Tutor' }}</p>
                <div class="mt-2 flex flex-wrap justify-center md:justify-start gap-4">
                    <span class="text-xs font-bold text-slate-400"><i
                            class="fa-solid fa-envelope mr-1 text-cyan-500"></i> {{ Auth::user()->email }}</span>
                    <span class="text-xs font-bold text-slate-400"><i class="fa-solid fa-phone mr-1 text-cyan-500"></i>
                        {{ $profile->phone ?? 'Not Set' }}</span>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('tutor.dashboard') }}"
                    class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-sm hover:bg-slate-200 transition-all">Dashboard</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            {{-- Sidebar Nav --}}
            <div class="space-y-2">
                <button wire:click="setSection('view_all')"
                    class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all font-bold text-sm {{ $activeSection == 'view_all' ? 'bg-cyan-600 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-100' }}">
                    <i class="fa-solid fa-eye w-5"></i> Full Profile View
                </button>
                <hr class="my-4 border-slate-200">
                <button wire:click.prevent="setEditor"
                    class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all font-bold  {{ $editor == true ? 'text-cyan-700 shadow-sm text-lg font-bold' : 'text-sm bg-white text-slate-500 border border-slate-100 hover:bg-cyan-600 hover:text-white' }}"
                    @disabled($editor ? true : false)>
                    <i
                        class="fa-solid fa-edit w-5"></i>{{ $editor
                            ? 'Editing Profile'
                            : 'Edit
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            Profile Data' }}</button>
                @php
                    $navItems = [
                        ['id' => 'personal', 'icon' => 'user', 'label' => 'Personal Info'],
                        ['id' => 'academic', 'icon' => 'graduation-cap', 'label' => 'Academics'],
                        ['id' => 'banking', 'icon' => 'wallet', 'label' => 'Banking'],
                        ['id' => 'media', 'icon' => 'clapperboard', 'label' => 'Media & Video'],
                    ];
                @endphp
                @if ($editor === true)
                    @foreach ($navItems as $item)
                        <button wire:click="setSection('{{ $item['id'] }}')"
                            class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all font-bold text-sm {{ $activeSection == $item['id'] ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-200' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100' }}">
                            <i class="fa-solid fa-{{ $item['icon'] }} w-5"></i>
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                @endif

            </div>

            {{-- Main Content Area --}}
            <div class="lg:col-span-3">

                @if (session()->has('success'))
                    <div
                        class="mb-4 p-4 bg-emerald-500 text-white rounded-2xl font-bold flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div
                        class="mb-4 p-4 bg-red-100 text-red-600 rounded-2xl font-bold flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
                    </div>
                @endif

                {{-- FULL VIEW ONLY SECTION --}}
                <div class="{{ $activeSection == 'view_all' ? 'block' : 'hidden' }} space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="bg-slate-800 p-6">
                            <h3 class="text-white font-black text-lg">Full Profile Record</h3>
                            <p class="text-slate-400 text-xs">Complete overview of your tutor registry data.</p>
                        </div>

                        <div class="p-8 space-y-8">
                            {{-- Personal Information Grid --}}
                            <section>
                                <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">Personal
                                    Details</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl">
                                    <div>
                                        <label class="block text-slate-400 text-[9px] font-black uppercase">Full
                                            Name</label>
                                        <p class="text-slate-800 font-bold">{{ $fullName ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">Gender</label>
                                        <p class="text-slate-800 font-bold">{{ $gender ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-[9px] font-black uppercase">Date of
                                            Birth</label>
                                        <p class="text-slate-800 font-bold">{{ $DOB ?? 'N/A' }}</p>
                                    </div>
                                    <div class="">
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">State</label>
                                        <p class="text-slate-800 font-bold">{{ $state ?? 'N/A' }}</p>
                                    </div>
                                    <div class="">
                                        <label class="block text-slate-400 text-[9px] font-black uppercase">City</label>
                                        <p class="text-slate-800 font-bold">{{ $city ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">Address</label>
                                        <p class="text-slate-800 font-bold">{{ $address ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </section>

                            {{-- Academic Grid --}}
                            <section>
                                <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">Academic
                                    & Professional</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl">
                                    <div>
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">Qualification</label>
                                        <p class="text-slate-800 font-bold">{{ $qualification ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">Specialization</label>
                                        <p class="text-slate-800 font-bold">{{ $discipline ?? 'N/A' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-slate-400 text-[9px] font-black uppercase">Career
                                            Profile</label>
                                        <p class="text-slate-700 text-sm leading-relaxed">
                                            {{ $careerProfile ?? 'No bio provided.' }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-slate-400 text-[9px] font-black uppercase">Experience</label>
                                        <span
                                            class="inline-block bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full text-xs font-black">{{ $experience ?? 'N/A' }}</span>
                                    </div>

                                </div>
                            </section>

                            {{-- Banking --}}
                            <section>
                                <h4 class="text-cyan-600 font-black uppercase text-[10px] tracking-widest mb-4">Payment
                                    & Banking</h4>
                                <div class="bg-slate-900 text-white p-6 rounded-2xl flex flex-wrap gap-8">
                                    <div>
                                        <label class="block text-slate-500 text-[9px] font-black uppercase">Bank</label>
                                        <p class="font-bold tracking-tight">{{ $bankName ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-slate-500 text-[9px] font-black uppercase">Account
                                            Name</label>
                                        <p class="font-bold tracking-tight">{{ $accountName ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-slate-500 text-[9px] font-black uppercase">Account
                                            Number</label>
                                        <p class="font-bold tracking-tight text-emerald-400">
                                            {{ $accountNumber ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </section>

                            {{-- Section 3: Media & Assets --}}
                            <div class="grid">
                                <div class="">
                                    <h4 class="text-xs font-black text-cyan-600 uppercase tracking-widest mb-1">Media
                                        Assets</h4>
                                    <p class="text-xs text-slate-400 font-medium">Introduction video and documents.</p>
                                </div>
                                <div class=" space-y-4">
                                    <div
                                        class="relative items-center bg-white border border-slate-200 p-4 rounded-2xl shadow-sm min-h-[200px] sm:min-h-[250px] md:min-h-[300px] lg:min-h-[350px] xl:min-h-[400px]">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                                                <i class="fa-solid fa-file-pdf text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black text-slate-800">Curriculum Vitae (CV)</p>
                                                <p class="text-[10px] text-slate-400">PDF Document</p>
                                            </div>
                                        </div>

                                        @if ($profile && $profile->CV)
                                            <div class="relative w-full mt-4">
                                                <iframe src="{{ asset('storage/' . $profile->CV) }}"
                                                    class="w-full h-[200px] sm:h-[250px] md:h-[300px] lg:h-[350px] xl:h-[400px] rounded-xl border border-slate-200"></iframe>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-rose-400">NOT UPLOADED</span>
                                        @endif
                                    </div>

                                    @if ($profile && $profile->video)
                                        <div
                                            class="bg-slate-900 rounded-2xl overflow-hidden shadow-xl border-4 border-slate-800">
                                            <video controls class="w-full aspect-video">
                                                <source src="{{ asset('storage/' . $profile->video) }}">
                                            </video>
                                            <div class="p-3 bg-slate-800 text-center">
                                                <p
                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                    Introduction Video Preview</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Personal Form --}}
                <div class="{{ $activeSection == 'personal' ? 'block' : 'hidden' }} space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm">01</span>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Full Legal
                                    Name</label>
                                <input type="text" wire:model="fullName"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:ring-cyan-500 font-medium">
                                @error('fullName')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Phone
                                    Number</label>
                                <input type="text" wire:model="phone"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:ring-cyan-500 font-medium">
                                @error('phone')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Date of
                                    Birth</label>
                                <input type="date" wire:model="DOB"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:ring-cyan-500 font-medium">
                                @error('DOB')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Gender</label>
                                <select wire:model="gender"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                @error('gender')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">State of
                                    Residence</label>
                                <select wire:model="state"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                    <option value="">Select State</option>
                                    @foreach ($states as $stateOption)
                                        <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">City</label>
                                <input type="text" wire:model="city"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:ring-cyan-500 font-medium">
                                @error('city')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Residential
                                    Address</label>
                                <textarea wire:model="address" rows="2" class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium"></textarea>
                                @error('address')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="savePersonal"
                                class="bg-cyan-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-cyan-700 transition-all shadow-lg shadow-cyan-100">
                                <span wire:loading wire:target='savePersonal' class="flex items-center gap-3"><i
                                        class="fas fa-spinner animate-spin mr-1"></i> Saving...</span> <span
                                    wire:loading.class='hidden' class="">Save Progress</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Academic Form --}}
                <div class="{{ $activeSection == 'academic' ? 'block' : 'hidden' }} space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm">02</span>
                            Academic Credentials
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Highest
                                    Qualification</label>
                                <select wire:model="qualification"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                    <option value="">Select Qualification</option>
                                    <option value="SSCE">SSCE</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="NCE">NCE</option>
                                    <option value="HND/Bachelors">BSc / HND</option>
                                    <option value="MSc/MA">MSc / MA</option>
                                    <option value="PhD">PhD</option>
                                </select>
                                @error('qualification')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Specialization /
                                    Discipline</label>
                                <input type="text" wire:model="discipline"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium"
                                    placeholder="e.g. Mathematics">
                                @error('discipline')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Career Bio
                                    (Philosophy)</label>
                                <textarea wire:model="careerProfile" rows="5" class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium"></textarea>
                                @error('careerProfile')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1 w-full">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Experience</label>
                                <select wire:model="experience"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                    <option value="">Select Experience</option>
                                    <option value="0-1 year">0–1 year</option>
                                    <option value="2-5 years">2–5 years</option>
                                    <option value="6-10 years">6–10 years</option>
                                    <option value="Above 10 years">Above 10 years</option>

                                </select>
                                @error('experience')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1 w-full">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Upload Updated
                                    CV
                                    (PDF | 2MB Max)</label>
                                <input type="file" wire:model="CV"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-cyan-50 file:text-cyan-700 font-medium">
                                @error('CV')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                                @if ($profile && $profile->CV)
                                    <a href="{{ asset('storage/' . $profile->CV) }}" target="_blank"
                                        class="text-[10px] font-bold text-cyan-600 mt-2 block underline">VIEW
                                        CURRENT
                                        CV</a>
                                @endif
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="saveAcademic"
                                class="bg-cyan-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-cyan-100">
                                <span wire:loading wire:target='saveAcademic' class="flex items-center gap-3"><i
                                        class="fas fa-spinner animate-spin mr-1"></i> Saving...</span> <span
                                    wire:loading.class='hidden' class="">Update Credentials</span></button>
                        </div>
                    </div>
                </div>

                {{-- Banking Form --}}
                <div class="{{ $activeSection == 'banking' ? 'block' : 'hidden' }}">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <div class="flex items-start gap-4 mb-8 bg-amber-50 p-4 rounded-2xl border border-amber-100">
                            <i class="fa-solid fa-shield-check text-amber-500 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-black text-amber-800 uppercase tracking-tight">Security Notice
                                </h4>
                                <p class="text-xs text-amber-700 font-medium">Ensure these details match your BVN
                                    records to avoid disbursement delays.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Bank Name</label>
                                <input type="text" wire:model="bankName"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                @error('bankName')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Account
                                    Number</label>
                                <input type="text" wire:model="accountNumber"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                @error('accountNumber')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Account Holder
                                    Name</label>
                                <input type="text" wire:model="accountName"
                                    class="w-full px-4 py-3 rounded-xl border-slate-200 font-medium">
                                @error('accountName')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="saveBanking"
                                class="bg-slate-800 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-slate-200 transition-all hover:bg-black">
                                <span wire:loading wire:target='saveBanking' class="flex items-center gap-3"><i
                                        class="fas fa-spinner animate-spin mr-1"></i> Saving...</span> <span
                                    wire:loading.class='hidden' class="">Secure Payment Details</span>

                            </button>
                        </div>
                    </div>
                </div>

                {{-- Media Form --}}
                <div class="{{ $activeSection == 'media' ? 'block' : 'hidden' }} space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h4 class="font-black text-slate-800">Introduction Video</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">A short 2-3 minutes video explaining
                                    your teaching methodology increases booking rates by 40%. (12MB Max)</p>
                                <input type="file" wire:model="video"
                                    class="block w-full text-xs text-slate-500 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold">
                                @error('video')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror

                                @if ($profile && $profile->video)
                                    <video controls class="w-full rounded-2xl border-4 border-slate-50 shadow-sm mt-4">
                                        <source src="{{ asset('storage/' . $profile->video) }}">
                                    </video>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <h4 class="font-black text-slate-800">Profile Image</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">Upload a professional headshot. Clear
                                    lighting and a plain background are recommended. (2MB Max) </p>
                                <input type="file" wire:model="image"
                                    class="block w-full text-xs text-slate-500 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:font-bold">
                                @error('image')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                                @if ($profile && $profile->image)
                                    <img src="{{ asset('storage/' . $profile->image) }}"
                                        class="w-full rounded-2xl border-4 border-slate-50 shadow-sm mt-4 object-cover h-40">

                                    </img>
                                @endif

                                <div wire:loading wire:target="image" class="text-cyan-600 text-[10px] font-bold">
                                    UPLOADING...</div>
                            </div>
                        </div>
                        <div class="mt-8 pt-8 border-t border-slate-50 flex justify-end">
                            <button wire:click="saveMedia"
                                class="bg-cyan-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-cyan-100">
                                <span wire:loading wire:target='saveMedia' class="flex items-center gap-3"><i
                                        class="fas fa-spinner animate-spin mr-1"></i> Saving...</span> <span
                                    wire:loading.class='hidden' class="">Save Media Files</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
