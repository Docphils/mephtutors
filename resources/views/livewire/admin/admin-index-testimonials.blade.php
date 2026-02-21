<div>
    <x-slot name="header">
        <div class="flex items-center">
            <a wire:navigate href="{{ route('admin.dashboard') }}"
                class="mr-2 px-2 py-1 bg-cyan-600 text-white font-semibold hover:shadow-lg hover:border hover:bg-cyan-900 rounded"><i
                    class="fa fa-arrow-left" aria-hidden="true"></i></a>
            <h2 class="font-semibold text-2xl text-cyan-800 leading-tight w-full">
                {{ __('Manage Testimonials') }}
            </h2>
        </div>
    </x-slot>

    <section class="mx-auto px-6 py-5 h-full">
        <!-- Success message -->
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-cyan-100 text-cyan-800 rounded-md">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                {{ session('error') }}
            </div>
        @endif
        <h3 class="text-3xl font-semibold text-white text-center ">What Clients Say</h3>
        <div class="flex items-center gap-4 w-full justify-end">
            <label for="status" class="text-bold">Status:</label>
            <select wire:model.live="status" class="text-gray-900" id="status">
                <option value="">All</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="WelcomePage">Welcome Page</option>
                <option value="Hidden">Hidden</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">

            @if ($testimonials->isEmpty())
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                    <p class="text-gray-600 italic">"There are no testimonials at the moment."</p>
                    <p class="mt-4 text-cyan-800 font-bold">- MephEd</p>
                </div>
            @else
                @foreach ($testimonials as $testimonial)
                    <div wire:key='$testimonial->id'
                        class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                        <p class="text-gray-600 italic">"{{ $testimonial->comment }}"</p>
                        <div class="flex justify-between my-4 items-center">
                            <p class="text-gray-800 font-bold">- {{ $testimonial->user->name }}</p>
                            <p class="text-gray-800 rounded-md px-3 py-1 bg-cyan-100">{{ $testimonial->status }}</p>
                        </div>
                        <div class="flex justify-between ">
                            <button wire:click='delete({{ $testimonial->id }})'
                                wire:confirm='Are you sure you want to delete this testimonial?'
                                class="text-white bg-red-500 hover:bg-red-600 hover:shadow-lg px-3 py-1 rounded-md">Delete</button>
                            <button wire:click='edit({{ $testimonial->id }})' wire:loading.attr="disabled"
                                class="text-white bg-cyan-500 hover:bg-cyan-600 hover:shadow-lg px-3 py-1 rounded-md">Update</button>
                        </div>

                        <i wire:loading wire:target="edit({{ $testimonial->id }}),delete({{ $testimonial->id }})"
                            class="fas fa-spinner text-cyan-800 text-xl ml-4"></i>
                    </div>
                @endforeach
                <!-- Pagination Links -->
                <div class="mt-4 md:col-span-3" wire:preserve-scroll>
                    {{ $testimonials->links() }}
                </div>
            @endif


        </div>

    </section>
    <!--Update Modal -->
    @if ($openModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 text-black">
            <div class="absolute w-5/6 sm:w-1/2 bg-white text-cyan-950 shadow-lg z-30 opacity-100 rounded-md">
                <div class="bg-cyan-900 text-white text-xl font-semibold w-full border-b border-white px-4 py-2">
                    Update Testimonial
                </div>
                <form wire:submit.prevent="update({{ $testimonial->id }})" class="p-4">
                    <label for="statusUpdate" class="block text-gray-700 font-medium mb-2">Status</label>
                    <select wire:model="statusUpdate" id="statusUpdate"
                        class="w-full text-gray-900 border border-gray-300 rounded-lg p-2 mb-4">
                        <option value="">All</option>
                        <option value="Approved">Approved</option>
                        <option value="Pending">Pending</option>
                        <option value="WelcomePage">Welcome Page</option>
                        <option value="Hidden">Hidden</option>
                    </select>
                    <div class="flex justify-between items-center mt-4">
                        <button wire:click="$set('openModal', false)" type="reset"
                            class="px-4 py-2 bg-gray-200 text-md font-semibold rounded hover:shadow-lg hover:bg-gray-100">
                            Cancel
                        </button>
                        <div wire:loading class="text-cyan-800">
                            <i class="fas fa-spinner" aria-label="Loading spinner"></i>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-cyan-600 text-white text-md font-semibold rounded hover:shadow-lg hover:bg-cyan-900">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
