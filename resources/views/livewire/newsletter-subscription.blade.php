<div class="p-3  shadow-md rounded-md mx-auto">
    <div class="flex items-center justify-center">
        <label class="inline-flex items-center">
            @if ($isSubscribed)
                <input type="checkbox" wire:model="isSubscribed" wire:change="toggleSubscription"
                    class="form-checkbox h-4 w-4 text-cyan-600 rounded-sm" checked>
                <span class="ml-2 text-sm text-cyan-100">Unsubscribe from our newsletter</span>
            @endif
            @if (!$isSubscribed)
                <input type="checkbox" wire:model="isSubscribed" wire:change="toggleSubscription"
                    class="form-checkbox h-4 w-4 text-cyan-600 rounded-sm">
                <span class="ml-2 text-sm text-cyan-100">Subscribe to our newsletter</span>
            @endif
            
        </label>
    </div>

    @if (session()->has('n_success'))
        <div class="mt-2 p-3 bg-green-100 text-green-700 rounded">
            {{ session('n_success') }}
        </div>
    @endif
</div>
