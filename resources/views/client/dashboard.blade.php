<x-app-layout>
    <div class="grid sm:grid-cols-5">
        <!-- Sidebar-->
        <section class="bg-gradient-to-t from-cyan-500 to-cyan-900 shadow-lg shadow-cyan-600 pl-16 pr-6 p-6 sm:py-16 border-l-4">
            <div class="hidden sm:block mb-6">
                @if ($userProfile)
                <div class="text-center mb-4">
                    <div class="flex justify-center">
                        <img src="{{ asset('storage/' . $userProfile->image) }}" alt="Profile image" class="h-14 w-14 rounded-full object-cover border-2 border-white shadow-sm shadow-white">
                    </div>
                    <p class="mb-2 font-semibold text-lg">{{ $user->name}}</p>
                </div>
                <div class="">Gender: {{ $userProfile->gender }}</div>
                <div class="">Address: {{ $userProfile->address }}</div>
                <div class="flex items-center">
                    <h4 class="text-md font-semibold px-2 py-1 rounded-lg bg-white">
                        <a href="{{ route('userProfile.edit', ['id' => $userProfile->id]) }}" class="text-cyan-600 hover:underline">Edit Profile</a>
                    </h4>                                                                        
                </div>
                @endif
            </div>
            <hr class="hidden sm:block w-full mb-4">
            <div class="">
                <div class="text-lg text-cyan-200 font-semibold underline">Coding And Robotics</div>
                <ul class="list-disc list-yellow-400">
                    <a href=""><li class="flex items-center hover:underline">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300 dark:text-green-400 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                         </svg>Coding Training</li>
                    </a>
                    <a href=""><li class="flex items-center hover:underline">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300 dark:text-green-400 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                         </svg>Coding Club/Hiring</li>
                    </a>
                    <a href=""><li class="flex items-center hover:underline">
                        <svg class="w-3.5 h-3.5 me-2 text-cyan-300 dark:text-green-400 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                         </svg>Robotics</li>
                    </a>
                </ul>
                <div class="text-lg text-white font-semibold underline mt-4">Music</div>
                    <ul class="list-disc list-yellow-400">
                        <a href=""><li class="flex items-center text-cyan-100 hover:underline">
                            <svg class="w-3.5 h-3.5 me-2 text-white dark:text-green-400 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                            </svg>Get Music Teacher</li>
                        </a>
                    </ul>
            </div>

        </section>
        
        <div class="sm:col-span-4 ">
            <x-slot name="header">
                <h2 class="font-semibold text-xl text-cyan-200 dark:text-gray-200 leading-tight">
                    {{ __('Client Dashboard') }}
                </h2>
            </x-slot>
        <!-- Main-->
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-cyan-200 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-2xl mb-4">Welcome, {{ $user->name }}!</h3>
        
                            @if (!$userProfile)
                                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                                    <strong>Profile Incomplete!</strong> Please <a href="{{ route('userProfile.create') }}" class="text-blue-500 hover:underline">update your profile</a> to enjoy full features.
                                </div>
                            @endif
        
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-green-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M6 2a1 1 0 000 2h8a1 1 0 000-2H6zM3 5a1 1 0 011-1h12a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V5zm10 6a1 1 0 112 0v2a1 1 0 11-2 0v-2zm-4 0a1 1 0 112 0v2a1 1 0 11-2 0v-2zM7 9a1 1 0 000 2h2a1 1 0 000-2H7z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"> <a href="{{ route('client.tutorRequests.create') }}" class="text-blue-500 hover:underline">Request Tutor</a></h4>
                                            <p>Request a tutor for yourself or wards.</p>
                                        </div>
                                    </div>
                                </div>
        
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-blue-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 00-1 1v14a1 1 0 001.707.707l5-5A1 1 0 0015 11H9V3a1 1 0 00-1-1zM3 7a1 1 0 011-1h4a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7zm0 4a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-1z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"><a href="{{ route('client.tutorRequests.index') }}" class="text-blue-500 hover:underline">View/Edit Tutor Requests</a></h4>
                                            <p>View and edit your requested sessions.</p>
                                        </div>
                                    </div>
                                </div>
        
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-yellow-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 3a1 1 0 00-1 1v12a1 1 0 002 0V4a1 1 0 00-1-1zM8 4a1 1 0 00-1 1v10a1 1 0 002 0V5a1 1 0 00-1-1zm6 2a1 1 0 00-1 1v8a1 1 0 002 0V7a1 1 0 00-1-1zm4 3a1 1 0 00-1 1v4a1 1 0 002 0v-4a1 1 0 00-1-1z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"><a href="{{ route('client.lessons') }}" class="text-blue-500 hover:underline">All Booked Lessons</a></h4>
                                            <p>View all your booked lessons.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-yellow-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 3a1 1 0 00-1 1v12a1 1 0 002 0V4a1 1 0 00-1-1zM8 4a1 1 0 00-1 1v10a1 1 0 002 0V5a1 1 0 00-1-1zm6 2a1 1 0 00-1 1v8a1 1 0 002 0V7a1 1 0 00-1-1zm4 3a1 1 0 00-1 1v4a1 1 0 002 0v-4a1 1 0 00-1-1z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"><button class="btn text-blue-500 hover:underline" onclick="ongoingLessons.showModal()">Active Lessons</button></h4>
                                            <p>View your ongoing lessons.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-yellow-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 3a1 1 0 00-1 1v12a1 1 0 002 0V4a1 1 0 00-1-1zM8 4a1 1 0 00-1 1v10a1 1 0 002 0V5a1 1 0 00-1-1zm6 2a1 1 0 00-1 1v8a1 1 0 002 0V7a1 1 0 00-1-1zm4 3a1 1 0 00-1 1v4a1 1 0 002 0v-4a1 1 0 00-1-1z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"><button class="btn text-blue-500 hover:underline" onclick="completedLessons.showModal()">Completed Lessons</button></h4>
                                            <p>Review and approve your completed lessons.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                    <div class="flex items-center">
                                        <svg class="w-12 h-12 text-yellow-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 3a1 1 0 00-1 1v12a1 1 0 002 0V4a1 1 0 00-1-1zM8 4a1 1 0 00-1 1v10a1 1 0 002 0V5a1 1 0 00-1-1zm6 2a1 1 0 00-1 1v8a1 1 0 002 0V7a1 1 0 00-1-1zm4 3a1 1 0 00-1 1v4a1 1 0 002 0v-4a1 1 0 00-1-1z"/>
                                        </svg>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-semibold"><button class="btn text-blue-500 hover:underline" onclick="closedLessons.showModal()">Closed Lessons</button></h4>
                                            <p>View closed lessons</p>
                                        </div>
                                    </div>
                                </div>
        
                                
                            </div>
        
                            <div class="mt-8">
                                <h3 class="text-xl mb-4">Statistics</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                        <h4 class="text-lg font-semibold">Total Tutor Requests</h4>
                                        <p class="text-3xl mt-2">{{$tutorRequests->count() }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
                                        <h4 class="text-lg font-semibold">Completed Lessons </h4>
                                        <h4 class="text-sm">Please review and approve within 24hrs</h4>
                                        <p class="text-3xl mt-2">{{ $completedBookings->count() }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-900 p-6 rounded shadow">
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
    
    <!--Modals-->
    <!-- Ongoing Lessons Modal -->
    <dialog id="ongoingLessons" class="modal modal-bottom sm:modal-middle p-6 w-1/2">
        <div class="modal-box">
            <div class="text-cyan-800 text-xl mb-4">Active/Assigned Lessons</div>

            <div class="grid sm:grid-cols-2 gap-8 mx-auto sm:px-6 lg:px-8 ">
                @foreach($ongoingBookings as $lesson)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold">{{ $lesson->subjects }}</h3>
                            <div class="flex justify-between">
                                <p><strong>Start Date:</strong> {{ $lesson->start_date }}</p>
                                <p><strong>End Date:</strong> {{ $lesson->end_date }}</p>
                            </div>
                            <a href="{{ route('bookings.show', $lesson->id) }}" class="text-blue-500">View Details</a>
                            <a href="{{ route('bookings.edit', $lesson->id) }}" class="text-blue-500 ml-4">Edit</a>
                            <form action="{{ route('bookings.destroy', $lesson->id) }}" method="POST" class="inline-block ml-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="modal-action">
            <form method="dialog">
                <!-- if there is a button in form, it will close the modal -->
                <button class="btn text-red-500">Close</button>
            </form>
            </div>
        </div>
    </dialog>
    <!-- Completed Lessons Modal -->
    <dialog id="completedLessons" class="modal modal-bottom sm:modal-middle p-6 overflow-y-visible w-1/2">
        <div class="modal-box">
            <div class="text-cyan-800 text-xl mb-4">Completed Lessons</div>
            <div class="max-w-7xl grid sm:grid-cols-2 gap-8 mx-auto sm:px-6 lg:px-8 ">
                @foreach($completedBookings as $lesson)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold">{{ $lesson->subjects }}</h3>
                            <div class="flex justify-between">
                                <p><strong>Start Date:</strong> {{ $lesson->start_date }}</p>
                                <p><strong>End Date:</strong> {{ $lesson->location }}</p>
                            </div>
                            <a href="{{ route('bookings.show', $lesson->id) }}" class="text-blue-500">View Details</a>
                            <a href="{{ route('bookings.edit', $lesson->id) }}" class="text-blue-500 ml-4">Edit</a>
                            
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-action">
            <form method="dialog">
                <!-- if there is a button in form, it will close the modal -->
                <button class="btn text-red-500">Close</button>
            </form>
            </div>
        </div>
    </dialog>
    <!-- Closed Lessons Modal -->
    <dialog id="closedLessons" class="modal modal-bottom sm:modal-middle p-6 overflow-y-visible w-1/2">
        <div class="modal-box">
            <div class="text-cyan-800 text-xl mb-4">Closed Lessons</div>
            <div class="max-w-7xl grid sm:grid-cols-2 gap-8 mx-auto sm:px-6 lg:px-8 ">
                @foreach($closedBookings as $lesson)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold">{{ $lesson->subjects }}</h3>
                            <div class="flex justify-between">
                                <p><strong>Start Date:</strong> {{ $lesson->start_date }}</p>
                                <p><strong>End Date:</strong> {{ $lesson->location }}</p>
                            </div>
                            <a href="{{ route('bookings.show', $lesson->id) }}" class="text-blue-500">View Details</a>
                           
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-action">
            <form method="dialog">
                <button class="btn text-red-500">Close</button>
            </form>
            </div>
        </div>
    </dialog>
</x-app-layout>
@include('layouts.footer')

