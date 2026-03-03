<div class="relative h-full mb-2">
    @if (session()->has('success'))
        <div class="bg-green-50 text-green-700 p-2 rounded-lg mb-2 text-sm" id="success-message">
            {{ session('success') }}
        </div>
        <a href="https://chat.whatsapp.com/IF4t0n8DcKzEMGS0Tw7d6Q"
            class="flex w-full mx-auto mb-2 bg-green-500 text-center justify-center text-white px-4 py-2 rounded-lg hover:text-green-50 hover:bg-green-600 transition">
            Join Community
        </a>
    @endif

    @if (session()->has('error'))
        <div class="bg-rose-50 text-rose-700 p-2 rounded-lg mb-2 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-2">
        <div>
            <label for="service_item_id" class="block text-gray-700 font-semibold">Bootcamp Track</label>
            <select wire:model.live="service_item_id" id="service_item_id" name="service_item_id"
                class="w-full border rounded-lg focus:outline-none focus:ring" required disabled>
                <option value="">Select a track</option>
                <option value="{{ $service_item_id }}">{{ $service_item_name }}</option>
            </select>
            @error('service_item_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cohort_id" class="block text-gray-700 font-semibold">Cohort</label>
            <select wire:model="cohort_id" id="cohort_id" name="cohort_id"
                class="w-full border rounded-lg focus:outline-none focus:ring" @disabled(empty($availableCohorts)) required>
                <option value="">{{ empty($availableCohorts) ? 'Select a track first' : 'Select a cohort' }}
                </option>
                @foreach ($availableCohorts as $cohort)
                    <option value="{{ $cohort->id }}">
                        {{ $cohort->name }} ({{ $cohort->code }})
                    </option>
                @endforeach
            </select>
            @error('cohort_id')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-gray-700 font-semibold">Full Name</label>
            <input wire:model="name" type="text" id="name" name="name"
                class="w-full border rounded-lg focus:outline-none focus:ring" required>
            @error('name')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-4">
            <div class="w-full">
                <label for="email" class="block text-gray-700 font-semibold">Email</label>
                <input wire:model="email" type="email" id="email" name="email"
                    class="w-full border rounded-lg focus:outline-none focus:ring" required>
                @error('email')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="w-full">
                <label for="phone" class="block text-gray-700 font-semibold">Phone Number</label>
                <input wire:model="phone" type="tel" id="phone" name="phone"
                    class="w-full border rounded-lg focus:outline-none focus:ring" required>
                @error('phone')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="address" class="block text-gray-700 font-semibold">Address</label>
            <input wire:model="address" id="address" name="address"
                class="w-full border rounded-lg focus:outline-none focus:ring" required />
            @error('address')
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full bg-cyan-600 text-white px-4 py-2 rounded-lg hover:text-cyan-50 hover:bg-cyan-700 transition">
            Enroll
        </button>

        <div wire:loading wire:target="save" class="text-cyan-800 text-sm">Submitting...</div>
    </form>
</div>
