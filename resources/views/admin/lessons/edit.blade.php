<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-cyan-200  leading-tight">
            {{ __('Edit Lesson') }}
        </h2>
    </x-slot>

    <div class="py-12">
        @if ($errors->any())
            <div class="bg-red-100 text-red-600 mb-2 rounded-md w-2/3 mx-auto sm:px-6 lg:px-8">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="w-2/3 mx-auto sm:px-6 lg:px-8">
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 ">
                    <form method="POST" action="{{ route('bookings.update', $booking->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="tutor_request_id" class="block font-medium text-sm text-gray-700">Request
                                ID</label>
                            <input type="text" name="tutor_request_id" id="tutor_request_id"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->tutor_request_id }}">
                        </div>
                        <div class="mb-4">
                            <label for="start_date" class="block font-medium text-sm text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->start_date }}">
                        </div>
                        <div class="mb-4">
                            <label for="end_date" class="block font-medium text-sm text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->end_date }}">
                        </div>
                        <div class="mb-4">
                            <label for="location" class="block font-medium text-sm text-gray-700">Location</label>
                            <input type="text" name="location" id="location"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->location }}">
                        </div>
                        <div class="mb-4">
                            <label for="days_times" class="block font-medium text-sm text-gray-700">Days & Times</label>
                            <input type="text" name="days_times" id="days_times"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->days_times }}">
                        </div>
                        <div class="mb-4">
                            <label for="subjects" class="block font-medium text-sm text-gray-700">Subjects</label>
                            <input type="text" name="subjects" id="subjects"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->subjects }}">
                        </div>
                        <div class="mb-4">
                            <label for="learners" class="block font-medium text-sm text-gray-700">Names of
                                learners</label>
                            <input type="text" name="learners" id="learners"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->learners }}">
                        </div>
                        <div class="mb-4">
                            <label for="sessions" class="block font-medium text-sm text-gray-700">Sessions</label>
                            <input type="number" name="sessions" id="sessions"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->sessions }}">
                        </div>
                        <div class="mb-4">
                            <label for="duration" class="block font-medium text-sm text-gray-700">Duration</label>
                            <input type="text" name="duration" id="duration"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->duration }}">
                        </div>
                        <div class="mb-4">
                            <label for="tutorGender" class="block font-medium text-sm text-gray-700">Tutor
                                Gender</label>
                            <select name="tutorGender" id="tutorGender"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                <option value="Male" {{ $booking->tutorGender == 'Male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="Female" {{ $booking->tutorGender == 'Female' ? 'selected' : '' }}>Female
                                </option>
                                <option value="Any" {{ $booking->tutorGender == 'Any' ? 'selected' : '' }}>Any
                                </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="curriculum" class="block font-medium text-sm text-gray-700">Curriculum</label>
                            <select name="curriculum" id="curriculum"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                <option value="British" {{ $booking->curriculum == 'British' ? 'selected' : '' }}>
                                    British</option>
                                <option value="French" {{ $booking->curriculum == 'French' ? 'selected' : '' }}>French
                                </option>
                                <option value="Nigerian" {{ $booking->curriculum == 'Nigerian' ? 'selected' : '' }}>
                                    Nigerian</option>
                                <option value="Blended" {{ $booking->curriculum == 'Blended' ? 'selected' : '' }}>
                                    Blended</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                <option value="Pending" {{ $booking->status == 'Pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="Adjust" {{ $booking->status == 'Adjust' ? 'selected' : '' }}>For
                                    Adjustment</option>
                                <option value="Accepted" {{ $booking->status == 'Accepted' ? 'selected' : '' }}>
                                    Accepted</option>
                                <option value="Active" {{ $booking->status == 'Active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="Completed" {{ $booking->status == 'Completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="Declined" {{ $booking->status == 'Declined' ? 'selected' : '' }}>
                                    Declined</option>
                                <option value="Closed" {{ $booking->status == 'Closed' ? 'selected' : '' }}>Closed
                                </option>

                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="classes" class="block font-medium text-sm text-gray-700">Learners Class or
                                Age Range</label>
                            <input type="text" name="classes" id="classes"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->classes }}">
                        </div>
                        <div class="mb-4">
                            <label for="amount" class="block font-medium text-sm text-gray-700">Amount</label>
                            <input type="number" name="amount" id="amount"
                                class="form-input rounded-md shadow-sm mt-1 block w-full"
                                value="{{ $booking->amount }}">
                        </div>
                        <div class="mb-4">
                            <label for="client_id" class="block font-medium text-sm text-gray-700">Client</label>
                            <select name="client_id" id="client_id"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ $booking->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="tutor_id" class="block font-medium text-sm text-gray-700">Tutor</label>
                            <select name="tutor_id" id="tutor_id"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                @foreach ($tutors as $tutor)
                                    <option value="{{ $tutor->id }}"
                                        {{ $booking->tutor_id == $tutor->id ? 'selected' : '' }}>{{ $tutor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="paymentStatus" class="block font-medium text-sm text-gray-700">Payment
                                Status</label>
                            <select name="paymentStatus" id="paymentStatus"
                                class="form-select rounded-md shadow-sm mt-1 block w-full">
                                <option value="Pending" {{ $booking->paymentStatus == 'Pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="Paid" {{ $booking->paymentStatus == 'Paid' ? 'selected' : '' }}>Paid
                                </option>
                                <option value="Confirmed"
                                    {{ $booking->paymentStatus == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="paymentEvidence" class="block font-medium text-sm text-gray-700">Proof of
                                Payment</label>
                            <input type="file" id="paymentEvidence" name="paymentEvidence"
                                value="paymentEvidence" class="rounded-md shadow-sm mt-1 block w-full">
                        </div>
                        <div class="flex justify-between gap-1">
                            <a href="{{ route('lessons.index') }}" wire:navigate type="cancel"
                                class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">Cancel</a>
                            <button type="submit"
                                class="bg-cyan-700 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
