<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-cyan-200 leading-tight">
            {{ __('Your Tutor Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto mb-4 text-right mx-6">
            <a wire:navigate href="{{ route('client.tutorRequests.create') }}" class="mx-6 sm:mx-16 text-cyan-900 bg-cyan-300 p-1 rounded-sm font-semibold shadow-lg shadow-cyan-600">Make New Request</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-6 sm:mx-16">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 sm:mx-16">
                <strong class="font-bold">Failed!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Pending Requests -->
        <div class="text-cyan-50 text-3xl mb-2 mx-6 sm:mx-16">New Requests</div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-sm sm:rounded-md border border-b-2 border-b-cyan-900">
                <div class="px-6 p-3 sm:p-6 text-gray-900">
                    <div class="relative font-bold text-lg flex justify-between items-center px-6 p-3 border border-b-2 border-b-cyan-900">
                        <div class="w-full">Subjects</div>
                        <div class="w-full">Start Date</div>
                        <div class="w-full">End Date</div>
                        <div class="w-1/2">Action</div>
                    </div>
                    @foreach($pendingRequests as $request)
                        <div wire:key="{{ $request->id }}" class="relative flex justify-around w-full items-center px-6 py-3 border border-b-2 border-b-cyan-900">
                            <div class="w-full font-semibold">{{ $request->subjects }}</div>
                            <div class="w-full">{{ $request->start_date }}</div>
                            <div class="w-full">{{ $request->end_date }}</div>
                            <div class="w-1/2 ">
                                <a wire:navigate href="{{ route('client.tutorRequests.show', ['id' => $request->id]) }}" class="text-cyan-600 hover:text-cyan-100 hover:shadow-xl px-2 py-1 hover:border hover:border-cyan-700 hover:bg-cyan-700">Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Assigned Requests -->
        <div class="text-cyan-50 text-3xl mb-2 mx-6 sm:mx-16">Assigned Requests</div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
            <div class="bg-cyan-50 overflow-hidden shadow-sm rounded-sm sm:rounded-md border border-b-2 border-b-cyan-900">
                <div class="px-6 p-3 sm:p-6 text-gray-900">
                    <div class="relative font-bold text-lg flex justify-between items-center px-6 p-3 border border-b-2 border-b-cyan-900">
                        <div class="w-full">Subjects</div>
                        <div class="w-full">Start Date</div>
                        <div class="w-full">End Date</div>
                        <div class="w-1/2">Action</div>
                    </div>
                    @foreach($assignedRequests as $request)
                        <div wire:key="{{ $request->id }}" class="relative flex justify-around w-full items-center px-6 py-3 border border-b-2 border-b-cyan-900">
                            <div class="w-full font-semibold">{{ $request->subjects }}</div>
                            <div class="w-full">{{ $request->start_date }}</div>
                            <div class="w-full">{{ $request->end_date }}</div>
                            <div class="w-1/2 ">
                                <a wire:navigate href="{{ route('client.tutorRequests.show', ['id' => $request->id]) }}" class="text-cyan-600 hover:text-cyan-100 hover:shadow-xl px-2 py-1 hover:border hover:border-cyan-700 hover:bg-cyan-700">Details</a>
                            </div>
                            
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

