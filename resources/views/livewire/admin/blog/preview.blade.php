<section class="space-y-4" aria-label="Private article preview">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Private preview</p>
                <h1 class="break-words text-xl font-black text-slate-900">{{ $post->title }}</h1>
            </div>
            <a wire:navigate href="{{ route('admin.blog.edit', $post->id) }}"
                class="shrink-0 rounded-lg bg-cyan-800 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-700 focus-visible:ring-offset-2">Back to editor</a>
        </div>
    </x-slot>
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        <div>
            <p class="font-bold">Preview of the saved article</p>
            <p class="mt-1 text-xs leading-5">Save your changes in the editor before previewing. The article below uses the same formatting as the public blog.</p>
        </div>
        <span class="rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-bold capitalize">{{ $post->status }}</span>
    </div>
    @include('livewire.blog.article-content', ['post' => $post])
</section>
