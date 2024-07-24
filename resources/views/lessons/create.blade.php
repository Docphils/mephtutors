<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Lesson') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('lessons.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Start Date</label>
                            <input type="date" name="start_date" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">End Date</label>
                            <input type="date" name="end_date" class="mt-1 block w-full" required>
                        </div>
                        <!-- Add other fields similarly -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Location</label>
                            <input type="text" name="location" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Days & Times</label>
                            <input type="text" name="days_times" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Subjects</label>
                            <input type="text" name="subjects" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Learners</label>
                            <input type="text" name="learners" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sessions</label>
                            <input type="text" name="sessions" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Duration</label>
                            <input type="text" name="duration" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tutor</label>
                            <input type="text" name="tutor" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Curriculum</label>
                            <select name="curriculum" class="mt-1 block w-full" required>
                                <option value="British">British</option>
                                <option value="French">French</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="Blended">Blended</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                            <select name="status" class="mt-1 block w-full" required>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Amount</label>
                            <input type="text" name="amount" class="mt-1 block w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Remarks</label>
                            <textarea name="remarks" class="mt-1 block w-full"></textarea>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                                Create Lesson
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
