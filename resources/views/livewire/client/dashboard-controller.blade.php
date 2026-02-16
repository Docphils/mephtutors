<div class="grid sm:grid-cols-5">
    <!-- Sidebar-->
    <section class="flex justify-between sm:block bg-gradient-to-t from-cyan-500 to-cyan-900 shadow-lg shadow-cyan-600 sm:px-10 p-6 sm:py-10 border-l-4">
        <div class="hidden sm:block mb-6">
            <livewire:user-profile lazy="on-load">
        </div>
        <hr class="hidden sm:block w-full mb-4">
        <div class="space-y-4">
            <div class="flex items-center gap-2 sm:gap-4">
                <i class="fas fa-book-open text-cyan-300 w-4"></i>
                <a wire:navigate href="{{ route('client.tutorRequests.create') }}" class="p-1 hover:border hover:border-cyan-300">Get a Tutor</a>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <i class="fas fa-paper-plane text-cyan-300 w-4"></i>
                <a wire:navigate href="{{ route('client.tutorRequests.index') }}" class="p-1 hover:border hover:border-cyan-300">Your Requests</a>
            </div>
            
            <div class="flex items-center gap-2 sm:gap-4">
                <i class="fas fa-music text-cyan-300 w-4"></i>
                <a wire:navigate href="{{ route('clubRequest.index') }}" class="p-1 hover:border hover:border-cyan-300">Get Club Instructor</a>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <i class="fas fa-code text-cyan-300 w-4"></i>
                <a wire:navigate href="{{ route('codingRequest.index') }}" class="p-1 hover:border hover:border-cyan-300">Get Coding Tutor</a>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <i class="fas fa-chalkboard-teacher text-cyan-300 w-4"></i>
                <a wire:navigate href="{{ route('client.lessons') }}" class="p-1 hover:border hover:border-cyan-300">Manage Lessons</a>
            </div>

            <div class="self-end">
                @livewire('newsletter-subscription')
            </div>
        </div>

        
    </section>
    
    <div class="sm:col-span-4 ">
        <x-slot name="header">
            <div class="flex items-center">
                <h2 class="font-semibold text-lg text-cyan-200 leading-tight w-full">
                    {{ __('Cleint Dashboard') }}
                </h2>
                <div class="flex sm:hidden w-full justify-end">
                    <livewire:user-profile lazy="on-load">
                </div>
            </div>
        </x-slot>
        <!-- Main-->
        <div wire:offline class="text-center text-red-600 bg-red-100 px-5 py-1 rounded w-full">
            This device is currently offline.
        </div>
        <div class="py-12">
           
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-cyan-200 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl mb-4">Welcome, {{ $user->name }}!</h3>

    
                        @if (!$userProfile)
                            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                                <strong>Profile Incomplete!</strong> Please complete your profile to enjoy seemless services.
                            </div>
                        @endif
         
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white  p-6 rounded shadow">
                                <div class="flex items-center">
                                    <svg class="w-12 h-12 text-green-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M6 2a1 1 0 000 2h8a1 1 0 000-2H6zM3 5a1 1 0 011-1h12a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V5zm10 6a1 1 0 112 0v2a1 1 0 11-2 0v-2zm-4 0a1 1 0 112 0v2a1 1 0 11-2 0v-2zM7 9a1 1 0 000 2h2a1 1 0 000-2H7z"/>
                                    </svg>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-semibold"> <a wire:navigate href="{{ route('client.tutorRequests.create') }}" class="text-blue-500 hover:underline">Request Tutor</a></h4>
                                        <p>Request a tutor for yourself or wards.</p>
                                    </div>
                                </div>
                            </div>
    
                            <div class="bg-white p-6 rounded shadow">
                                <div class="flex items-center">
                                    <svg class="w-12 h-12 text-blue-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 00-1 1v14a1 1 0 001.707.707l5-5A1 1 0 0015 11H9V3a1 1 0 00-1-1zM3 7a1 1 0 011-1h4a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7zm0 4a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-1z"/>
                                    </svg>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-semibold"><a wire:navigate href="{{ route('client.tutorRequests.index') }}" class="text-blue-500 hover:underline">View/Edit Tutor Requests</a></h4>
                                        <p>View and edit your requested sessions.</p>
                                    </div>
                                </div>
                            </div>
    
                            <div class="bg-white p-6 rounded shadow">
                                <div class="flex items-center">
                                    <svg class="w-12 h-12 text-yellow-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M4 3a1 1 0 00-1 1v12a1 1 0 002 0V4a1 1 0 00-1-1zM8 4a1 1 0 00-1 1v10a1 1 0 002 0V5a1 1 0 00-1-1zm6 2a1 1 0 00-1 1v8a1 1 0 002 0V7a1 1 0 00-1-1zm4 3a1 1 0 00-1 1v4a1 1 0 002 0v-4a1 1 0 00-1-1z"/>
                                    </svg>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-semibold"><a wire:navigate href="{{ route('client.lessons') }}" class="text-blue-500 hover:underline">All Booked Lessons</a></h4>
                                        <p>View all your booked lessons.</p>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
    
                        <div class="mt-8">
                            <h3 class="text-xl mb-4">Statistics</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white p-6 rounded shadow">
                                    <h4 class="text-lg font-semibold">Total Tutor Requests</h4>
                                    <p class="text-3xl mt-2">{{$tutorRequests->count() }}</p>
                                </div>
                                <div class="bg-white p-6 rounded shadow">
                                    <h4 class="text-lg font-semibold">Completed Lessons </h4>
                                    <h4 class="text-sm">Please review and approve within 24hrs</h4>
                                    <p class="text-3xl mt-2">{{ $completedBookings->count() }}</p>
                                </div>
                                <div class="bg-white p-6 rounded shadow">
                                    <h4 class="text-lg font-semibold">Closed Lessons</h4>
                                    <p class="text-3xl mt-2">{{ $closedBookings->count() }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
