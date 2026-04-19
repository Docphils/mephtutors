    <!-- Edit Form -->
    <div class="h-screen">
        <x-slot name="header">
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Manage <span
                            class="text-cyan-600">Profile Data</span></h1>
                    <p class="text-slate-500 text-sm">Manage your profile details here.</p>
                </div>
                @can('Admin')
                    <a wire:navigate href="{{ route('admin.dashboard') }}"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-cyan-100 flex items-center gap-2 text-sm ">
                        <i class="fas fa-house-laptop"></i>
                        Dashboard
                    </a>
                @endcan
                @can('Client')
                    <a wire:navigate href="{{ route('client.dashboard') }}"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-cyan-100 flex items-center gap-2 text-sm ">
                        <i class="fas fa-house-laptop"></i>
                        Dashboard
                    </a>
                @endcan
            </div>
        </x-slot>
        <div wire:offline class="fixed top-6 right-6 z-50">
            <div class="flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-full shadow-2xl animate-pulse">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a5 5 0 010-7.072M4.929 19.071a9 9 0 010-12.728m0 0l2.829 2.829m-2.829-2.829L3 3">
                    </path>
                </svg>
                <span class="text-xs font-bold uppercase tracking-wider">System Offline</span>
            </div>
        </div>

        <!-- Modal Container -->
        <form wire:submit.prevent="save" enctype="multipart/form-data"
            class="relative w-4xl max-w-4/5 bg-white text-gray-900 rounded-lg shadow-lg p-6 z-30 h-auto max-h-[90vh] overflow-y-auto">
            <!-- Fullname Field -->
            <div class="mb-4">
                <label for="fullname" class="block text-md font-medium text-gray-900">Your Full Name</label>
                <input type="text" wire:model="fullname"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                @error('fullname')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Phone Field -->
                <div class="">
                    <label for="phone" class="block text-md font-medium text-gray-900">Phone</label>
                    <input type="tel" wire:model="phone"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                    @error('phone')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <!-- State Field -->
                <div class="">
                    <label for="state" class="block text-md font-medium text-gray-900">State</label>
                    <select type="text" wire:model="state"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                        <option value="">Select State</option>
                        @foreach ($states as $stateOption)
                            <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                        @endforeach
                    </select>
                    @error('state')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- City Field -->
                <div class="">
                    <label for="city" class="block text-md font-medium text-gray-900">City</label>
                    <input type="text" wire:model="city"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                    @error('city')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address Field -->
                <div class="sm:col-span-2">
                    <label for="address" class="block text-md font-medium text-gray-900">Address</label>
                    <input type="text" wire:model="address"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                    @error('address')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <!-- Gender Field -->
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="">
                    <label for="gender" class="block text-md font-medium text-gray-900">Gender</label>
                    <select wire:model="gender"
                        class="mt-1 block w-full text-gray-900 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    @error('gender')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Date of Birth Field -->
                <div class="">
                    <label for="DOB" class="block text-md font-medium text-gray-900">Date of Birth</label>
                    <input type="date" wire:model="DOB"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required>
                    @error('DOB')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Profile Image Field -->
            <div class="mb-4">
                <label for="image" class="block text-md font-medium text-gray-900">Profile Image</label>
                <input type="file" wire:model="image"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('image')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror

                @if ($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" alt="Profile Preview" class="h-14 w-14 rounded-full">
                    </div>
                @elseif ($userProfile && $userProfile->image)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $userProfile->image) }}" alt="Profile Image"
                            class="h-14 w-14 rounded-full">
                    </div>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-800 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:shadow-outline-blue disabled:opacity-25 transition ease-in-out duration-150">
                    Save
                </button>
                @can('Client')
                    <a wire:navigate href="{{ route('client.dashboard') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md flex items-center gap-2"> <i
                            class="fas fa-house-shield"></i> Dashboard </a>
                @endcan
                @can('Admin')
                    <a wire:navigate href="{{ route('admin.dashboard') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md flex items-center gap-2"> <i
                            class="fas fa-house-shield"></i> Dashboard </a>
                @endcan
            </div>
        </form>
    </div>
