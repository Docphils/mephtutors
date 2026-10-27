<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-cyan-200 dark:text-gray-200 leading-tight">
            {{ __('Your Booked Lessons') }}
        </h2>
    </x-slot>        
    
    <div class="container mx-auto p-4">
        <div class="mx-auto mb-4 ">
            <a wire:navigate href="{{ route('client.dashboard') }}" class="mx-6 text-cyan-900 bg-cyan-500 p-2 rounded-lg font-semibold shadow-lg shadow-cyan-600 hover:bg-cyan-400">Dashboard</a>
        </div>

       <livewire:client.lessons lazy="on-load" />
    </div>
    @include('layouts.footer')
</x-app-layout>
