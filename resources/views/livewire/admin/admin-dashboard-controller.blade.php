<div>
    <div wire:offline class="text-center text-red-600 bg-red-100 px-5 py-1 rounded">
        This device is currently offline.
    </div>
    <div class="grid sm:grid-cols-5">
        <!-- Sidebar-->
        <section class="flex justify-between sm:block bg-gradient-to-t from-cyan-500 to-cyan-900 shadow-lg shadow-cyan-600 sm:px-10 p-6 sm:py-10 border-l-4">
            <div class="hidden sm:block mb-6">
                <livewire:user-profile lazy="on-load">
            </div>
            <hr class="hidden sm:block w-full mb-4">
            <div class="">
                <div class="text-lg text-cyan-200 font-semibold underline">Admin Panel</div>
                <ul class="list-disc list-yellow-400">
                    <a href="{{ route('admin.users') }}" wire:navigate><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Manage Users</li>
                    </a>
                    <a wire:navigate href="{{route('tutorRequests.index')}}"><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Manage Requests</li>
                    </a>
                    <a href="{{route('lessons.index')}}" wire:navigate><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Manage Lessons</li>
                    </a>
                    <a href="{{route('admin.payments.index')}}"><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Manage Payments</li>
                    </a>
                    <a href="{{route('admin.crm.index')}}"><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Coding & Clubs</li>
                    </a>
                    <a href="{{route('admin.tutorProfile')}}" wire:navigate><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Tutor Profiles</li>
                    </a>
                    <a href="{{route('admin.newsletter')}}" wire:navigate><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Newsletter</li>
                    </a>
                    <a href="{{route('admin.testimonials')}}" wire:navigate><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Testimonials</li>
                    </a>
                    <button type="button" wire:click.prevent="$set('Main', 'Clients')"><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>All Clients</li>
                    </button>
                    <button type="button" wire:click.prevent="$set('Main', 'ContactMessages')"><li class="flex items-center hover:underline hover:text-cyan-100 mb-4">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300  flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>Messages</li>
                    </button>
                </ul>
            </div>
        </section>
        
        <div class="sm:col-span-4 ">
            <x-slot name="header">
                <div class="flex items-center">
                    <h2 class="font-semibold text-lg text-cyan-200 leading-tight w-full">
                        {{ __('Admin Dashboard') }}
                    </h2>
                    <div class="flex sm:hidden w-full justify-end">
                        <livewire:user-profile lazy="on-load">
                    </div>
                </div>
            </x-slot>
            <div class=" py-8 px-10 text-lg bg-cyan-500">
                
            </div>

            
            <!-- Main-->
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    @if ($Main === 'tutorDetails')
                        <livewire:admin.tutorprofile-manager />
                    @elseif ($Main === 'Clients')
                        <livewire:admin.client-manager />
                    @elseif ($Main === 'ContactMessages')
                        <livewire:admin.contact-messages />
                    @else
                    
                        <div class="bg-cyan-200  overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 ">
                                <h3 class="text-2xl mb-4">Welcome, {{ $user->name }}!</h3>
            
                                @if (!$userProfile)
                                    <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                                        <strong>Profile Incomplete!</strong> Please complete your profile to enjoy seemless services.
                                    </div>
                                @endif
            
                                <div class="mt-8">
                                    <h3 class="text-xl mb-4">Statistics</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">Total Users</h4>
                                            <p class="text-3xl mt-2">{{ $users->count() }}</p>
                                        </div>
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">Total Tutor Requests</h4>
                                            <p class="text-3xl mt-2">{{ $tutorRequests->count() }}</p>
                                        </div>
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">Active Bookings</h4>
                                            <p class="text-3xl mt-2">{{ $bookings->count() }}</p>
                                        </div>
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">Completed Bookings</h4>
                                            <p class="text-3xl mt-2">{{ $completedBookings->count() }}</p>
                                        </div>
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">Payments Earned</h4>
                                            <p class="text-3xl mt-2">{{ $earnedPayments->count() }}</p>
                                        </div>
                                        <div class="bg-white  p-6 rounded shadow">
                                            <h4 class="text-lg font-semibold">New Coding/Music Requests</h4>
                                            <p class="text-3xl mt-2">{{ $newCRM->count() }}</p>
                                        </div>
                                    </div>
                                </div>
            
                            </div>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</div>
