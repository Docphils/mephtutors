<div class="max-w-7xl m-10 mx-auto p-8 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="font-black text-lg sm:text-2xl  text-slate-800 tracking-tight leading-tight">
                        Tutor Profile <span class="text-cyan-600">Manager</span>
                    </h2>
                    <p class="hidden sm:block text-slate-500 text-xs sm:text-sm font-medium mt-1">
                        Review and manage the profile of tutors.
                    </p>
                </div>
            </div>

            {{-- <button x-on:click="$dispatch('open-new-booking')"
                class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-cyan-200 transition-all flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>New Booking</span>
            </button> --}}
        </div>
    </x-slot>
    <!-- Success message -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
            {{ session('success') }}
        </div>
    @endif
    <!-- Tabs for Qualification and Discipline Filtering -->
    <div class="mb-6 flex items-center justify-between flex-wrap gap-2">
        <input type="text" wire:model.live="search" placeholder="Search by name or address..."
            class="text-black px-4 py-2 border rounded-lg w-full sm:w-1/3">

        <div class="sm:flex gap-2 space-y-2 sm:space-y-0 w-full sm:w-1/3 justify-end">
            <!-- Qualification Select -->
            <select wire:change="setTab($event.target.value)"
                class="px-4 py-2 rounded-lg text-white bg-cyan-700 hover:bg-cyan-800">
                <option value="All" {{ $activeTab == 'All' ? 'selected' : '' }}>All</option>
                <option value="SSCE" {{ $activeTab == 'SSCE' ? 'selected' : '' }}>SSCE</option>
                <option value="Diploma" {{ $activeTab == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                <option value="NCE" {{ $activeTab == 'NCE' ? 'selected' : '' }}>NCE</option>
                <option value="HND/BSc/BEd/BA/BEng" {{ $activeTab == 'HND/BSc/BEd/BA/BEng' ? 'selected' : '' }}>Degree
                </option>
                <option value="MSc/MA" {{ $activeTab == 'MSc/MA' ? 'selected' : '' }}>MSc/MA</option>
                <option value="PhD" {{ $activeTab == 'PhD' ? 'selected' : '' }}>PhD</option>
            </select>

            <!-- Discipline Select -->
            <select wire:change="setTab($event.target.value)"
                class="px-4 py-2 rounded-lg text-white bg-emerald-700 hover:bg-emerald-800">
                <option value="Science" {{ $activeTab == 'Science' ? 'selected' : '' }}>Science</option>
                <option value="Arts" {{ $activeTab == 'Arts' ? 'selected' : '' }}>Arts</option>
                <option value="Commerce" {{ $activeTab == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                <option value="Approved" {{ $activeTab == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Pending" {{ $activeTab == 'Pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
    </div>

    <!-- Display Tutor Profiles -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="min-w-full divide-y divide-gray-200">
            <div class="bg-cyan-50">
                <div class="grid grid-cols-3 sm:grid-cols-5 pr-4 gap-2">
                    <div class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase divacking-wider">Full
                        Name</div>
                    <div class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Qual.
                    </div>
                    <div
                        class="hidden sm:block px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                        Exp.</div>
                    <div
                        class="hidden sm:block px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                        Status</div>
                    <div class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions
                    </div>
                </div>
            </div>
            <div class="bg-white divide-y divide-gray-200 overflow-x-auto w-full">
                @forelse($tutorProfiles as $profile)
                    <div class="grid grid-cols-3 sm:grid-cols-5 pr-4 gap-2">
                        <div class="px-4 py-3 text-sm text-gray-700">{{ $profile->fullName }}</div>
                        <div class="px-4 py-3 text-sm text-gray-700">
                            @php
                                if ($profile->qualification === 'HND/BSc/BEd/BA/BEng') {
                                    $qualification = 'Degree';
                                } else {
                                    $qualification = $profile->qualification;
                                }
                            @endphp
                            {{ $qualification }}</div>
                        <div class="hidden sm:block px-4 py-3 text-sm text-gray-700">{{ $profile->experience }}</div>
                        <div class="hidden sm:block px-4 py-3 text-sm text-gray-700">
                            @php
                                if ($profile->Approved) {
                                    $status = 'Approved';
                                } else {
                                    $status = 'Pending';
                                }
                            @endphp
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status == 'Approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $status }}
                            </span>
                        </div>
                        <div class="px-4 py-3 text-sm text-gray-700">
                            <button wire:click="showTutorProfile({{ $profile->id }})"
                                class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">Show</button>
                            <button wire:click="editTutorProfile({{ $profile->id }})"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</button>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-gray-500 col-span-3 sm:col-span-5 text-center">
                        No tutor profiles found.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="p-4">
            {{ $tutorProfiles->links() }}
        </div>
    </div>

    <!-- Modal for viewing tutor profile details -->
    @if ($showDetailModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 text-black">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg relative max-h-[80vh] overflow-y-auto">
                <button wire:click="$set('showDetailModal', false)"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    &times;
                </button>
                <h3 class="text-2xl font-semibold text-cyan-700 mb-4 text-center">Tutor Profile Details</h3>

                <div class="p-4 bg-cyan-50 rounded-md mb-4">
                    <div class="flex gap-4">
                        <!-- Full Name -->
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Full Name:</p>
                            <p class="text-gray-900">{{ $selectedProfile->fullName }}</p>
                        </div>
                        <!-- Discipline -->
                        <div class="w-2/3">
                            <p class="font-semibold text-gray-700">Discipline:</p>
                            <p class="text-gray-900">{{ $selectedProfile->discipline }}</p>
                        </div>
                    </div>


                    <div class="flex gap-4 ">
                        <!-- Experience -->
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Experience:</p>
                            <p class="text-gray-900">{{ $selectedProfile->experience }}</p>
                        </div>

                        <!-- Qualification -->
                        <div class="w-2/3">
                            <p class="font-semibold text-gray-700">Qualification:</p>
                            <p class="text-gray-900">{{ $selectedProfile->qualification }}</p>
                        </div>
                    </div>
                </div>
                <!-- Contact Details Section -->
                <div class="p-4 bg-blue-50 rounded-md mb-4">
                    <h4 class="font-semibold text-blue-700 mb-2">Contact Details</h4>
                    <p><strong>Phone:</strong> {{ $selectedProfile->phone }}</p>
                    <p><strong>Email:</strong> {{ $selectedProfile->user->email }}</p>
                    <p><strong>Address:</strong> {{ $selectedProfile->address }}</p>
                </div>

                <!-- Bank Details Section -->
                <div class="p-4 bg-green-50 rounded-md mb-4">
                    <h4 class="font-semibold text-green-700 mb-2">Bank Details</h4>
                    <p><strong>Bank Name:</strong> {{ $selectedProfile->bankName }}</p>
                    <p><strong>Account Name:</strong> {{ $selectedProfile->accountName }}</p>
                    <p><strong>Account Number:</strong> {{ $selectedProfile->accountNumber }}</p>
                </div>

                <!-- Professional Information Section -->
                <div class="p-4 bg-purple-50 rounded-md mb-4">
                    <h4 class="font-semibold text-purple-700 mb-2">Professional Information</h4>
                    <p><strong>Career Profile:</strong> {{ $selectedProfile->careerProfile }}</p>
                    @php
                        if ($selectedProfile->Approved) {
                            $profileStatus = 'Approved';
                        } else {
                            $profileStatus = 'Pending';
                        }
                    @endphp
                    <p><strong>Status:</strong> <span
                            class="{{ $profileStatus == 'Approved' ? 'text-green-600' : 'text-yellow-600' }}">{{ $profileStatus }}</span>
                    </p>
                </div>

                <!-- Additional Info Section -->
                <div class="p-4 bg-yellow-50 rounded-md mb-4">
                    <h4 class="font-semibold text-yellow-700 mb-2">Additional Information</h4>
                    <p><strong>Date of Birth:</strong> {{ $selectedProfile->DOB }}</p>
                    <p><strong>Gender:</strong> {{ $selectedProfile->gender }}</p>
                </div>

                <!-- CV Preview Section -->
                <div class="p-4 bg-gray-100 rounded-md mb-4">
                    <h4 class="font-semibold text-gray-800 mb-2">CV</h4>
                    @if ($selectedProfile->CV)
                        <div class="bg-white border rounded-md overflow-hidden">
                            <iframe src="{{ asset('storage/' . $selectedProfile->CV) }}" width="100%" height="500px"
                                class="rounded-md">
                            </iframe>
                        </div>
                    @else
                        <p class="text-gray-500">No CV uploaded</p>
                    @endif
                </div>


                <!-- Video Preview Section -->
                <div class="p-4 bg-pink-50 rounded-md mb-4">
                    <h4 class="font-semibold text-pink-700 mb-2">Professional Video</h4>
                    @if ($selectedProfile->video)
                        <video controls autoplay muted loop preload="auto" playsinline
                            class="w-full rounded-lg max-h-48">
                            <source src="{{ asset('storage/' . $selectedProfile->video) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <p class="text-gray-500">No video uploaded</p>
                    @endif
                </div>

                <!-- Close Button -->
                <div class="mt-6 flex justify-center">
                    <button wire:click="$set('showDetailModal', false)"
                        class="px-6 py-2 bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-600 focus:outline-none">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif


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
