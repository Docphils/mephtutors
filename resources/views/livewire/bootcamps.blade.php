<div class="relative h-full mb-2">
    @if (session()->has('success'))
        <div class="bg-green-50 text-green-600 p-2 rounded-lg mb-2 text-sm" id="success-message">
            {{ session('success') }}
        </div>
        <a href="https://chat.whatsapp.com/IF4t0n8DcKzEMGS0Tw7d6Q" class="flex w-1/2 mx-auto mb-2 bg-green-500 text-center text-white px-4 py-2 rounded-lg hover:text-green-50 hover:bg-green-600 transition">Join Community</a>
    @endif
    <form wire:submit.prevent='save' class="">
        <div class="mb-2">
            <label for="name" class="block text-gray-700 font-semibold">Full Name</label>
            <input wire:model='name' type="text" id="name" name="name" class="w-full  border rounded-lg focus:outline-none focus:ring " required>
        </div>
        <div class="mb-2 flex gap-4">
            <div class="w-full">
                <label for="email" class="block text-gray-700 font-semibold">Email</label>
                <input  wire:model='email' type="email" id="email" name="email" class="w-full  border rounded-lg focus:outline-none focus:ring " required>
            </div>
            <div class="w-full">
                <label for="phone" class="block text-gray-700 font-semibold">Phone Number</label>
                <input  wire:model='phone' type="tel" id="phone" name="phone" class="w-full  border rounded-lg focus:outline-none focus:ring " required>
            </div>
        </div>
        <div class="mb-2">
            <label for="address" class="block text-gray-700 font-semibold">Address</label>
            <input  wire:model='address' id="address" name="address" class="w-full  border rounded-lg focus:outline-none focus:ring " required />
        </div>
        <button type="submit" class="w-full bg-cyan-600 text-white px-4 py-2 rounded-lg hover:text-cyan-50 hover:bg-cyan-700 transition">Enroll</button>
        <div wire:loading class="text-cyan-800"> Submitting... </div>
    </form>
</div>
