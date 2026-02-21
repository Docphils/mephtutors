<div class=" w-full mx-auto">

    <div>
        <!-- Profile Display -->
        @if ($userProfile)
            <div class="sm:text-center mb-2">
                <div class="flex justify-center text-sm">
                    <img src="{{ asset('storage/' . $userProfile->image) }}" alt="Profile image"
                        class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm shadow-white">
                </div>
                <div class="hidden sm:block ">
                    <p class="font-semibold">{{ $userProfile->user->name }}</p>
                </div>
                <div class="sm:flex justify-center items-center gap-4 hidden">
                    <p class="text-xs text-green-400">{{ strToUpper($userProfile->user->role) }}</p>
                    <button wire:click="openProfileModal"
                        class="fas fa-edit bg-cyan-600 hover:bg-cyan-800 text-white px-2 py-1 rounded-md text-xs"></button>
                </div>

            </div>
        @else
            Click the button to update your profile

            <button wire:click="openProfileModal"
                class="bg-cyan-600 hover:bg-cyan-800 text-white px-1 sm:px-2 sm:py-1 rounded-md text-xs sm:text-sm">Update
                profile</button>
        @endif

        @if (session()->has('success'))
            <div class="bg-green-500 text-white p-2 rounded-lg mb-2 text-sm" id="success-message">
                {{ session('success') }}
            </div>
        @endif
    </div>
</div>
