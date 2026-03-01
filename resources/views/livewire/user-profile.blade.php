<div class=" w-full mx-auto">

    <div>
        <!-- Profile Display -->
        @if ($userProfile)
            <div class="text-center mb-2">
                <div class="flex justify-center text-sm">
                    <img src="{{ asset('storage/' . $userProfile->image) }}" alt="Profile image"
                        class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm shadow-white">
                </div>
                <div class="block ">
                    <p class="font-semibold">{{ $userProfile->user->name }}</p>
                </div>
                <div class="flex justify-center items-center gap-4">
                    <p class="text-xs text-green-400">{{ strToUpper($userProfile->user->role) }}</p>
                    <a class="bg-cyan-600 hover:bg-cyan-800 text-white px-2 py-1 rounded-md text-xs"
                        href="{{ route('userProfile') }}" wire:navigate><i class="fas fa-edit "></i></a>
                </div>

            </div>
        @endif

        @if (session()->has('success'))
            <div class="bg-green-500 text-white p-2 rounded-lg mb-2 text-sm" id="success-message">
                {{ session('success') }}
            </div>
        @endif
    </div>
</div>
