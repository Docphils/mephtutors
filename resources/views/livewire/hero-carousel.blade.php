<div x-data="{
    current: 0,
    total: {{ count($items) }},
    interval: null,
    next() { this.current = (this.current + 1) % this.total },
    prev() { this.current = (this.current - 1 + this.total) % this.total },
    init() { this.interval = setInterval(() => this.next(), 7000) },
    destroy() { clearInterval(this.interval) }
}" x-init="init()" x-on:mouseleave="init()" x-on:mouseenter="clearInterval(interval)"
    tabindex="0" class="h-[80vh] w-full relative">

    @foreach ($items as $index => $item)
        <div x-show="current === {{ $index }}" x-transition.opacity
            class="absolute inset-0 h-full bg-cover bg-center"
            style="background-image: url('{{ asset('storage/' . $item->image_path ?? '/images/default.jpg') }}');">
            <div class="h-full bg-black/45 flex flex-col items-center justify-center text-white px-4">
                <h2 class="text-2xl sm:text-3xl md:text-5xl font-bold">{{ $item->name ?? 'Our Service' }}</h2>
                <p class="mt-3 text-sm sm:text-md md:text-xl text-cyan-100">{{ $item->description ?? '' }}</p>
                <a href="{{ $item->target === 'bootcamp'
                    ? route('apply.bootcamp', ['serviceItem' => $item->slug])
                    : ($item->target === 'institutions'
                        ? route('apply.crm', ['serviceItem' => $item->slug])
                        : route('apply.tutor', ['serviceItem' => $item->slug])) }}"
                    class="z-20 mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow hover:bg-pink-700">
                    Apply Now
                </a>
            </div>
        </div>
    @endforeach

    <!-- Controls -->
    <div class="absolute inset-0 flex items-center justify-between px-4">
        <button @click="prev()"
            class="bg-black/40 hover:bg-black/60 text-white rounded-full w-12 h-12 flex items-center justify-center">‹</button>
        <button @click="next()"
            class="bg-black/40 hover:bg-black/60 text-white rounded-full w-12 h-12 flex items-center justify-center">›</button>
    </div>

    <!-- Indicators -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex gap-2">
        <template x-for="i in total" :key="i">
            <button @click="current = i-1" :class="{ 'bg-white': current === i - 1, 'bg-white/50': current !== i - 1 }"
                class="w-3 h-3 rounded-full"></button>
        </template>
    </div>
</div>
