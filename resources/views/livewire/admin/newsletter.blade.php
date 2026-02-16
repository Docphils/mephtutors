<div class="p-6 bg-white shadow-sm rounded-md max-w-3xl mx-auto">
    <x-slot name="header">
        <div class="mb-4">
            <a wire:navigate href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg text-white bg-cyan-700 hover:bg-cyan-900 hover:shadow-xl shadow-md">
                Dashboard
            </a>
        </div>
    </x-slot>
    <h2 class="text-2xl font-semibold mb-4 text-cyan-700">Compose Newsletter</h2>

    <form wire:submit.prevent="sendNewsletter" class="space-y-4 text-gray-900">
        <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" wire:model="subject"
                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" wire:model="title"
                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div x-data="{ body: @entangle('body').defer }">
            <label class="block text-sm font-medium text-gray-700">Body</label>
            <input id="body-input" type="hidden" x-model="body">
            <trix-editor input="body-input" x-on:trix-change="$wire.body = $event.target.value"></trix-editor>
            @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
       
        <div>
            <label class="block text-sm font-medium text-gray-700">Paragraph 2 <span class="text-xs opacity-80"> (optional)</span> </label>
            <textarea wire:model="body2" rows="5"
                      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            @error('body2') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700"> Select Recipients</label>
            <select wire:model="recipients" class="px-4 py-2 rounded-lg border border-gray-300 w-full">
                <option value="All">All Subscribed Users</option>
                <option value="Clients">Clients</option>
                <option value="Admins">Admins</option>
                <option value="Tutors">Tutors</option>
                <option value="TestTutor">Test Tutor</option>
                <option value="TutorsWithProfile">Tutors with Profile</option>
                <option value="TutorsWithoutProfile">Tutors without Profile</option>
            </select>
        </div>

        <div class="flex gap-6">
            <button type="submit"
                    class="bg-cyan-800 text-white px-4 py-2 rounded-md hover:bg-cyan-700 focus:outline-none">
                Send Newsletter
            </button>
            <div wire:loading wire:target="sendNewsletter" class="text-cyan-600">Sending ....</div>
        </div>
    </form>

    @if (session()->has('success'))
        <div class="mt-2 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-2 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    
</div>
