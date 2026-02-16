<div class="container mx-auto px-4 py-6">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-cyan-200 dark:text-gray-200 leading-tight">
                {{ __('Request Tutor') }}
            </h2>
        </x-slot>

        @if (session('success'))
            <div class="bg-green-50 p-1 my-1 text-green-600 ">
                {{ session('success') }}
            </div>
        @endif
    
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    
        <div class="py-6">
            <div class="w-full sm:w-2/3 mx-auto sm:px-2 lg:px-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 ">
                        <form wire:submit.prevent="submit">
                            @csrf
                            <div class="mb-4 sm:flex justify-between gap-3">
                                <div class="w-full">
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Intended Start Date</label>
                                    <input type="date" wire:model="start_date" id="start_date" class="mt-1 block w-full" required>
                                    @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-full">
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">Expected End Date</label>
                                    <input type="date" wire:model="end_date" id="end_date" class="mt-1 block w-full" required>
                                    @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="location" class="block text-sm font-medium text-gray-700">Lesson Location & Address</label>
                                <input type="text" wire:model="location" id="location" class="mt-1 block w-full" required>
                                @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="days" class="block text-sm font-medium text-gray-700">Days & Times</label>
                                <input type="text" wire:model="days_times" id="days" class="mt-1 block w-full" placeholder="Mondays: 5:00PM; Fridays: 4:30PM" required>
                                @error('days_times') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="subjects" class="block text-sm font-medium text-gray-700 ">Subjects</label>
                                <input type="text" wire:model="subjects" id="subjects" class="mt-1 block w-full" placeholder="Science, Mathematics, ..." required>
                                @error('subjects') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="learners" class="block text-sm font-medium text-gray-700">Learners Names</label>
                                <input type="text" wire:model="learners" id="learners" class="mt-1 block w-full" placeholder="Isah Adams, and Abel John" required>
                                @error('learners') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4 sm:flex justify-between gap-3">
                                <div class="w-full">
                                    <label for="sessions" class="block text-sm font-medium text-gray-700">Number of Contacts in session</label>
                                    <input type="text" wire:model="sessions" id="sessions" class="mt-1 block w-full" placeholder="8 Contacts" required>
                                    @error('sessions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-full">
                                    <label for="duration" class="block text-sm font-medium text-gray-700">Daily Duration</label>
                                    <input type="text" wire:model="duration" id="duration" class="mt-1 block w-full" placeholder="1 hour daily" required>
                                    @error('duration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                            <div class="mb-4 sm:flex justify-between gap-3">
                                <div class="w-full">
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Preferred Tutor's Gender</label>
                                    <select wire:model="tutor_gender" id="gender" class="mt-1 block w-full" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Any">Any Gender</option>
                                    </select>
                                    @error('tutor_gender') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-full">
                                    <label for="curriculum" class="block text-sm font-medium text-gray-700 ">Curriculum</label>
                                    <select wire:model="curriculum" id="curriculum" class="mt-1 block w-full" required>
                                        <option value="British">British</option>
                                        <option value="French">French</option>
                                        <option value="Nigerian">Nigerian</option>
                                        <option value="Blended">Blended</option>
                                    </select>
                                    @error('curriculum') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                                               
                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700">Proposed Amount (NGN) </label>
                                <input type="text" wire:model="amount" id="amount" class="mt-1 block w-full" required>
                                @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea wire:model="remarks" id="remarks" class="mt-1 block w-full" placeholder="Type additional remarks here"></textarea>
                                @error('remarks') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex items-center justify-between mt-4">
                                <button type="reset" onclick="window.location='{{ route('client.tutorRequests.index')}}'" class="bg-gray-600 hover:bg-gray-400 text-white px-4 py-2 rounded-md">Cancel</button>

                                <div wire:loading class="bg-cyan-100 text-cyan-900 px-6 py-1">Submitting....</div>
                                <button type="submit" class="bg-cyan-700 hover:bg-cyan-900 text-white px-2 sm:px-4 py-2 rounded-md">
                                    Request Tutor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>    
</div>
