<div>
    @if (session()->has('success'))
        <div class="bg-green-500 text-white p-2 rounded-lg mb-2 text-sm" id="success-message">
            {{ session('success') }}
        </div>
    @endif
    <form wire:submit.prevent='save' class="">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold">Name</label>
            <input wire:model='name' type="text" id="name" name="name"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300" required>
        </div>
        <div class="mb-4 md:flex gap-4">
            <div class="w-full mb-4 md:mb-0">
                <label for="email" class="block text-gray-700 font-semibold">Email</label>
                <input wire:model='email' type="email" id="email" name="email"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                    required>
            </div>
            <div class="w-full">
                <label for="phone" class="block text-gray-700 font-semibold">Phone Number</label>
                <input wire:model='phone' type="tel" id="phone" name="phone"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                    required>
            </div>
        </div>
        <div class="mb-4">
            <label for="message" class="block text-gray-700 font-semibold">Message</label>
            <textarea wire:model='message' id="message" name="message" rows="4"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300" required></textarea>
        </div>
        <button type="submit"
            class="w-full bg-cyan-800 text-white px-4 py-2 rounded-lg hover:text-cyan-100 hover:bg-cyan-900 transition">Send
            Message</button>
        <div wire:loading class="text-cyan-800"> Submitting... </div>
    </form>
</div>
