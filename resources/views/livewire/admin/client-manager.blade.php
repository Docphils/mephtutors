<div class="p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex items-center">
            <a wire:navigate href="{{ route('admin.dashboard') }}"
                class="mr-2 px-2 py-1 bg-cyan-600 text-white font-semibold hover:shadow-lg hover:border hover:bg-cyan-900 rounded"><i
                    class="fa fa-arrow-left" aria-hidden="true"></i></a>
            <div class="">
                <h2 class="font-semibold text-2xl text-cyan-800 leading-tight w-full">
                    {{ __('Manage Clients') }}
                </h2>
                <p class="text-xs font-bold">View and manage comprehensive clients details here</p>
            </div>
        </div>
    </x-slot>
    <!-- Success message -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
            {{ session('message') }}
        </div>
    @endif
    <!-- Tabs for Filtering -->
    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Search by name or address..."
            class="text-black px-4 py-2 border rounded-lg w-1/3 mb-4">
    </div>


    <!-- Display Client Details -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if (!$search)
            <div class="min-w-full divide-y divide-gray-200">
                <div class="bg-cyan-700 text-slate-100">
                    <div class="grid grid-cols-8 pr-4 text-xs font-medium uppercase tracking-wider">
                        <div class="px-4 py-3 text-left col-span-2">
                            Username</div>
                        <div class="px-4 py-3 text-left col-span-3">
                            Email</div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Requests</span>
                            <i class="fas fa-paper-plane md:hidden"></i>
                        </div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Lessons</span>
                            <i class="fas fa-chalkboard-teacher md:hidden"></i>
                        </div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Actions</span>
                            <i class="fas fa-tasks md:hidden"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white divide-y divide-gray-200">
                    @foreach ($clients as $client)
                        <div class="grid grid-cols-8 hover:bg-slate-50 pr-4">
                            <div class="px-4 py-3 text-sm text-gray-700 col-span-2">{{ $client->name }}</div>
                            <div class="px-4 py-3 text-sm text-gray-700  col-span-3 truncate">{{ $client->email }}</div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                {{ $client->tutorRequests()->count() }}
                                @if ($client->tutorRequests()->where('status', 'Pending')->exists())
                                    <sup class="font-features sups"><span class="text-xs text-red-600 rounded-md">
                                            {{ $client->tutorRequests()->where('status', 'Pending')->count() }}</span>
                                    </sup>
                                @endif
                            </div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                {{ $bookings->where('client_id', $client->id)->count() }}
                                @if ($bookings->where('client_id', $client->id)->where('status', 'Pending'))
                                    <sup class="font-features sups"><span class="text-xs text-cyan-600 rounded-md">
                                            {{ $bookings->where('client_id', $client->id)->where('status', 'Pending')->count() }}</span>
                                    </sup>
                                @endif
                            </div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                <button wire:click="showClientDetails({{ $client->id }})"
                                    class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 hidden md:block">Show</button>
                                <button wire:click="showClientDetails({{ $client->id }})"
                                    class="px-1  bg-gray-500 text-white rounded hover:bg-gray-600 md:hidden"><i
                                        class="fas fa-eye"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-4">
                {{ $clients->links() }}
            </div>
        @else
            <div class="min-w-full divide-y divide-gray-200">
                <div class="bg-cyan-700 text-slate-100 text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="grid grid-cols-8">
                        <div class="px-4 py-3 text-left col-span-2">
                            Username</div>
                        <div class="px-4 py-3 text-left col-span-3">
                            Email</div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Requests</span>
                            <i class="fas fa-paper-plane md:hidden"></i>
                        </div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Lessons</span>
                            <i class="fas fa-chalkboard-teacher md:hidden"></i>
                        </div>
                        <div class="px-4 py-3 text-left">
                            <span class="hidden md:block">Actions</span>
                            <i class="fas fa-tasks md:hidden"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white divide-y divide-gray-200">
                    @foreach ($getDetails as $client)
                        <div class="grid grid-cols-8 hover:bg-slate-50 pr-4">
                            <div class="px-4 py-3 text-sm text-gray-700 col-span-2">{{ $client->name }}</div>
                            <div class="px-4 py-3 text-sm text-gray-700  col-span-3">{{ $client->email }}</div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                {{ $client->tutorRequests()->count() }}
                                @if ($client->tutorRequests()->where('status', 'Pending')->exists())
                                    <sup class="font-features sups"><span class="text-xs text-red-600 rounded-md">
                                            {{ $client->tutorRequests()->where('status', 'Pending')->count() }}</span>
                                    </sup>
                                @endif
                            </div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                {{ $client->bookings()->count() }}
                                @if ($client->bookings()->where('status', 'Closed')->exists())
                                    <sup class="font-features sups"><span class="text-xs text-cyan-600 rounded-md">
                                            {{ $client->bookings()->where('status', 'Closed')->count() }}</span>
                                    </sup>
                                @endif
                            </div>
                            <div class="px-4 py-3 text-sm text-gray-700">
                                <button wire:click="showClientDetails({{ $client->id }})"
                                    class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 hidden md:block">Show</button>
                                <i
                                    class="fas fa-eye px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 md:hidden"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-4">
                {{ $getDetails->links() }}
            </div>
        @endif

    </div>


    <!-- Modal for viewing tutor profile details -->
    @if ($showDetailModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 text-black">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-xl relative max-h-[90vh] overflow-y-auto">
                <button wire:click="$set('showDetailModal', false)"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 focus:outline-none text-xl font-bold">
                    &times;
                </button>
                <div class="flex justify-between mb-4 items-center w-full">
                    <h3 class="col-span-3 relative w-full text-2xl font-semibold text-cyan-700 text-center">Client
                        Profile Details</h3>
                    <p
                        class="relative h-8 w-8 sm:h-12 sm:w-12 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-[10px] sm:text-xs shrink-0  border-2 border-cyan-700 overflow-hidden">
                        @if ($selectedClient->userProfile->image)
                            <img src="{{ asset('storage/' . $selectedClient->userProfile->image) }}"
                                alt="{{ $selectedClient->name }}'s image" class="relative object-cover">
                        @else
                            {{ strtoupper(substr($selectedClient->name, 0, 2)) }}
                        @endif
                    </p>
                </div>

                <div class="p-4 bg-cyan-50 rounded-md mb-4 text-sm">
                    <div class="flex gap-4 mb-2 border-b">
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Userame:</p>
                            <p class="text-gray-900">{{ $selectedClient->name }}</p>
                        </div>
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Email:</p>
                            <p class="text-gray-900">{{ $selectedClient->email }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4  mb-2 border-b">
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Phone:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->phone ?? '' }}</p>
                        </div>
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">State:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->state ?? '' }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4  mb-2 border-b">
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">City:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->city ?? '' }}</p>
                        </div>
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Address:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->address ?? '' }}</p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Full Name:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->fullname ?? '' }}</p>
                        </div>
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Date of Birth:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->date_of_birth ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Gender:</p>
                            <p class="text-gray-900">{{ $selectedClient->userProfile->gender ?? '' }}</p>
                        </div>
                        <div class="w-full">
                            <p class="font-semibold text-gray-700">Date Joined:</p>
                            <p class="text-gray-900">{{ $selectedClient->created_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Requests Section -->
                <div class="p-4 bg-blue-50 rounded-md mb-4  text-sm">
                    <h4 class="font-semibold text-blue-700 mb-2  text-lg">Tutor Requests</h4>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>Total Requests:</strong> {{ $selectedClient->tutorRequests()->count() }}</p>
                        </div>
                        <div class="w-full">
                            <p><strong>Pending Requests:</strong>
                                {{ $selectedClient->tutorRequests()->where('status', 'Pending')->count() }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>Assigned Requests:</strong>
                                {{ $selectedClient->tutorRequests()->where('status', 'Assigned')->count() }}</p>
                        </div>
                        <div class="w-full">
                            <p><strong>Cancelled Requests:</strong>
                                {{ $selectedClient->tutorRequests()->where('status', 'Cancelled')->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Lessons Section -->
                <div class="p-4 bg-yellow-50 rounded-md mb-4 text-sm">
                    <h4 class="font-semibold text-yellow-700 mb-2 text-lg">Lessons</h4>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>All Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->count() }}</p>
                        </div>
                        <div class="w-full">
                            <p class="text-yellow-600"><strong>Pending Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Pending')->count() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p class="text-red-600"><strong>Needs Adjustment:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Adjust')->count() }}
                            </p>
                        </div>
                        <div class="w-full">
                            <p class="text-blue-600"><strong>Accepted Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Accepted')->count() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p class="text-green-600"><strong>Active Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Active')->count() }}
                            </p>
                        </div>
                        <div class="w-full">
                            <p class="text-purple-600"><strong>Completed Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Completed')->count() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p class="text-red-600"><strong>Declined Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Declined')->count() }}
                            </p>
                        </div>
                        <div class="w-full">
                            <p class="text-cyan-600"><strong>Closed Lessons:</strong>
                                {{ $bookings->where('client_id', $selectedClient->id)->where('status', 'Closed')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Coding/Clubs Section -->
                <div class="p-4 bg-green-50 rounded-md mb-4  text-sm">
                    <h4 class="font-semibold text-blue-700 mb-2  text-lg">Coding/Club Requests</h4>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>Total Requests:</strong>
                                {{ $crms->where('user_id', $selectedClient->id)->count() }}</p>
                        </div>
                        <div class="w-full">
                            <p><strong>Pending Requests:</strong>
                                {{ $crms->where('user_id', $selectedClient->id)->where('status', 'Pending')->count() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>Active Requests:</strong>
                                {{ $crms->where('user_id', $selectedClient->id)->where('status', 'Ongoing')->count() }}
                            </p>
                        </div>
                        <div class="w-full">
                            <p><strong>Cancelled Requests:</strong>
                                {{ $crms->where('user_id', $selectedClient->id)->where('status', 'Cancelled')->count() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-4 mb-2 border-b ">
                        <div class="w-full">
                            <p><strong>Closed Requests:</strong>
                                {{ $crms->where('user_id', $selectedClient->id)->where('status', 'Closed')->count() }}
                            </p>
                        </div>

                    </div>
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

</div>
