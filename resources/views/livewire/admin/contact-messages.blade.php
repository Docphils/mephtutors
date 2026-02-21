<div class="p-6 bg-cyan-100 min-h-screen text-gray-900">
    <x-slot name="header">
        <div class="flex items-center">
            <a wire:navigate href="{{ route('admin.dashboard') }}"
                class="mr-2 px-2 py-1 bg-cyan-600 text-white font-semibold hover:shadow-lg hover:border hover:bg-cyan-900 rounded"><i
                    class="fa fa-arrow-left" aria-hidden="true"></i></a>
            <h2 class="font-semibold text-2xl text-cyan-800 leading-tight w-full">
                {{ __('Contact Messages') }}
            </h2>
        </div>
    </x-slot>

    <!-- Success message -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
            {{ session('message') }}
        </div>
    @endif
    <div class="max-w-5xl mx-auto">

        <!-- Search and Sorting Options -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 space-y-2 sm:space-y-0">
            <input type="text" wire:model="search" placeholder="Search messages by name or email"
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-1/3" />

            <div class="flex space-x-4 items-center">
                <button wire:click="sortBy('created_at')"
                    class="px-3 py-2 bg-cyan-600 text-white rounded-md hover:bg-blue-600 focus:outline-none flex items-center">
                    Date <i class="fas fa-sort ml-2"></i>
                </button>
                <button wire:click="sortBy('is_read')"
                    class="px-3 py-2 bg-cyan-500 text-white rounded-md hover:bg-green-600 focus:outline-none flex items-center">
                    Status <i class="fas fa-sort ml-2"></i>
                </button>
                <select wire:model="perPage" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                </select>
            </div>
        </div>

        <!-- Messages List -->
        <div class="bg-white shadow rounded-lg pr-4 mr-2">
            @forelse($messages as $message)
                <div
                    class="p-4 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 {{ $message->is_read ? 'bg-gray-100' : 'bg-white' }}">
                    <div class="flex-1">
                        <p class="text-lg font-semibold text-gray-800">{{ $message->name }}</p>
                        <p class="text-gray-600">{{ $message->email }}</p>
                        <p class="text-sm text-gray-500">Phone: {{ $message->phone }}</p>
                        <p class="text-sm text-gray-500">Submitted: {{ $message->created_at->format('Y-m-d') }}</p>
                        <p class="text-sm {{ $message->is_read ? 'text-green-600' : 'text-red-600' }}">
                            {{ $message->is_read ? 'Read' : 'Unread' }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="showMessage({{ $message->id }})"
                            class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 focus:outline-none">
                            View
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">
                    No messages found.
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $messages->links() }}
        </div>

        <!-- Message Details Modal -->
        @if ($show)
            <div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white p-6 w-11/12 sm:w-2/3 lg:w-1/2 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4 text-gray-800">Message Details</h2>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ $selectedMessage->name }}</p>
                        <p><strong>Email:</strong> {{ $selectedMessage->email }}</p>
                        <p><strong>Phone:</strong> {{ $selectedMessage->phone }}</p>
                        <p><strong>Message:</strong> {{ $selectedMessage->message }}</p>
                    </div>
                    <button wire:click="$set('show', null)"
                        class="mt-6 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none">
                        Close
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
